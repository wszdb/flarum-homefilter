<?php

namespace Wszdb\HomeFilter;

use Flarum\Extend;
use Flarum\Api\Controller\ListDiscussionsController;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\Discussion\Discussion;
use Flarum\Database\AbstractModel;
use Illuminate\Database\ConnectionInterface;
use Wszdb\HomeFilter\Listener\AdjustQueryLimit;
use Carbon\Carbon;

return [
    // 注册前端资源
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js'),
    
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    // 注册语言包
    new Extend\Locales(__DIR__.'/locale'),

    // 注册设置项
    (new Extend\Settings())
        ->serializeToForum('homefilter.keywords', 'wszdb-homefilter.keywords', function ($value) {
            return $value ? explode(',', $value) : [];
        })
        ->serializeToForum('homefilter.limit', 'wszdb-homefilter.limit', function ($value) {
            return (int)($value ?: 5);
        })
        ->serializeToForum('homefilter.filterMode', 'wszdb-homefilter.filter_mode', function ($value) {
            return $value ?: 'title';
        })
        ->serializeToForum('homefilter.supplementMode', 'wszdb-homefilter.supplement_mode', function ($value) {
            return $value ?: 'default';
        })
        ->serializeToForum('homefilter.supplementDays', 'wszdb-homefilter.supplement_days', function ($value) {
            return (int)($value ?: 7);
        })
        ->serializeToForum('homefilter.sortMode', 'wszdb-homefilter.sort_mode', function ($value) {
            return $value ?: 'time_desc';
        }),

    // ✅ 使用原版的 prepareDataForSerialization 方式
    (new Extend\ApiController(ListDiscussionsController::class))
        ->prepareDataForSerialization(function ($controller, &$data, $request, $document) {
            $settings = resolve(SettingsRepositoryInterface::class);
            
            // 获取配置
            $keywordsStr = $settings->get('wszdb-homefilter.keywords', '');
            $limit = (int)$settings->get('wszdb-homefilter.limit', 5);
            $filterMode = $settings->get('wszdb-homefilter.filter_mode', 'title');
            $supplementMode = $settings->get('wszdb-homefilter.supplement_mode', 'default');
            $supplementDays = (int)$settings->get('wszdb-homefilter.supplement_days', 7);
            $sortMode = $settings->get('wszdb-homefilter.sort_mode', 'time_desc');
            
            // 检查是否在首页
            $filterParams = $request->getQueryParams()['filter'] ?? [];
            $filterQ = $filterParams['q'] ?? '';
            $filterTag = $filterParams['tag'] ?? '';
            
            // 排除相关讨论查询
            $isRelatedDiscussions = isset($filterParams['nearataRelatedDiscussions']);
            
            if ($isRelatedDiscussions) {
                return;
            }
            
            // 仅在首页且有配置时过滤
            if (empty($filterQ) && empty($filterTag) && !empty($keywordsStr) && $limit > 0) {
                $keywords = array_filter(array_map('trim', explode(',', $keywordsStr)));
                
                if (!empty($keywords) && $data->count() > 0) {
                    $targetCount = (int)($request->getQueryParams()['page']['limit'] ?? 20);
                    $actor = $request->getAttribute('actor');
                    $db = resolve(ConnectionInterface::class);
                    
                    // ✅ 如果是标签过滤模式，预先查询所有帖子的标签
                    $discussionTags = [];
                    if ($filterMode === 'tags') {
                        $discussionIds = $data->pluck('id')->toArray();
                        if (!empty($discussionIds)) {
                            $tagResults = $db->table('discussion_tag')
                                ->join('tags', 'discussion_tag.tag_id', '=', 'tags.id')
                                ->whereIn('discussion_tag.discussion_id', $discussionIds)
                                ->select('discussion_tag.discussion_id', 'tags.name')
                                ->get();
                            
                            foreach ($tagResults as $row) {
                                if (!isset($discussionTags[$row->discussion_id])) {
                                    $discussionTags[$row->discussion_id] = [];
                                }
                                $discussionTags[$row->discussion_id][] = $row->name;
                            }
                        }
                    }
                    
                    // 第一步：分离置顶和非置顶帖子，标记要删除的项
                    $stickyDiscussions = [];
                    $normalDiscussions = [];
                    $toRemove = [];
                    $keywordCount = 0;
                    $keptIds = [];
                    $filteredIds = [];
                    
                    foreach ($data as $index => $discussion) {
                        $hasKeyword = false;
                        
                        if ($filterMode === 'tags') {
                            // ✅ 标签过滤模式
                            $tags = $discussionTags[$discussion->id] ?? [];
                            foreach ($tags as $tagName) {
                                foreach ($keywords as $keyword) {
                                    if (mb_strpos($tagName, $keyword) !== false) {
                                        $hasKeyword = true;
                                        break 2;
                                    }
                                }
                            }
                        } else {
                            // 标题过滤模式（原版逻辑）
                            if (isset($discussion->title)) {
                                foreach ($keywords as $keyword) {
                                    if (mb_strpos($discussion->title, $keyword) !== false) {
                                        $hasKeyword = true;
                                        break;
                                    }
                                }
                            }
                        }
                        
                        // 根据是否匹配关键词决定是否保留
                        if ($hasKeyword) {
                            if ($keywordCount < $limit) {
                                $keywordCount++;
                                $keptIds[] = $discussion->id;
                                // 分离置顶和非置顶
                                if ($discussion->is_sticky) {
                                    $stickyDiscussions[] = $discussion;
                                } else {
                                    $normalDiscussions[] = $discussion;
                                }
                            } else {
                                $toRemove[] = $index;
                                $filteredIds[] = $discussion->id;
                            }
                        } else {
                            $keptIds[] = $discussion->id;
                            // 分离置顶和非置顶
                            if ($discussion->is_sticky) {
                                $stickyDiscussions[] = $discussion;
                            } else {
                                $normalDiscussions[] = $discussion;
                            }
                        }
                    }
                    
                    // 删除标记的项
                    foreach (array_reverse($toRemove) as $index) {
                        $data->forget($index);
                    }
                    
                    // 重新索引
                    $data = $data->values();
                    
                    $needMore = $targetCount - $data->count();
                    
                    // 第二步：如果数量不够，从数据库补充
                    if ($needMore > 0) {
                        $allExcludedIds = array_merge($keptIds, $filteredIds);
                        
                        // ✅ 构建补充查询，过滤隐藏的 Tag
                        $supplementQuery = Discussion::query()
                            ->whereVisibleTo($actor)
                            ->whereNotIn('id', $allExcludedIds);
                        
                        // ✅ 关键修复1：过滤掉设置为隐藏的 Tag 的帖子
                        // 检查是否安装了 flarum/tags 扩展
                        if ($db->getSchemaBuilder()->hasTable('tags')) {
                            // 排除所有带有 is_hidden=1 标签的讨论
                            $hiddenTagIds = $db->table('tags')
                                ->where('is_hidden', 1)
                                ->pluck('id')
                                ->toArray();
                            
                            if (!empty($hiddenTagIds)) {
                                $supplementQuery->whereNotExists(function ($query) use ($hiddenTagIds, $db) {
                                    $query->select($db->raw(1))
                                        ->from('discussion_tag')
                                        ->whereColumn('discussion_tag.discussion_id', 'discussions.id')
                                        ->whereIn('discussion_tag.tag_id', $hiddenTagIds);
                                });
                            }
                        }
                        
                        // ✅ 新功能2：根据补充策略选择帖子
                        if ($supplementMode === 'unread_random') {
                            // 未读随机模式：从过去X天的未读帖子中随机选择
                            // ✅ 修复：使用 Carbon::now() 替代 now()
                            $daysAgo = Carbon::now()->subDays($supplementDays);
                            $supplementQuery->where('last_posted_at', '>=', $daysAgo);
                            
                            // 如果用户已登录，只选择未读的帖子
                            if (!$actor->isGuest()) {
                                $supplementQuery->whereNotExists(function ($query) use ($actor, $db) {
                                    $query->select($db->raw(1))
                                        ->from('discussion_user')
                                        ->whereColumn('discussion_user.discussion_id', 'discussions.id')
                                        ->where('discussion_user.user_id', $actor->id)
                                        ->whereColumn('discussion_user.last_read_post_number', '>=', 'discussions.last_post_number');
                                });
                            }
                            // 未登录用户：所有帖子都视为未读，不需要额外过滤
                            
                            // 随机排序
                            $supplementQuery->inRandomOrder();
                        } else {
                            // 默认模式：按时间倒序
                            $supplementQuery->orderBy('last_posted_at', 'desc');
                        }
                        
                        // 查询额外的帖子（多查一些以备过滤）
                        $additional = $supplementQuery->limit($needMore * 2)->get();
                        
                        // ✅ 如果是标签模式，查询补充帖子的标签
                        $additionalTags = [];
                        if ($filterMode === 'tags' && $additional->count() > 0) {
                            $additionalIds = $additional->pluck('id')->toArray();
                            
                            $tagResults = $db->table('discussion_tag')
                                ->join('tags', 'discussion_tag.tag_id', '=', 'tags.id')
                                ->whereIn('discussion_tag.discussion_id', $additionalIds)
                                ->select('discussion_tag.discussion_id', 'tags.name')
                                ->get();
                            
                            foreach ($tagResults as $row) {
                                if (!isset($additionalTags[$row->discussion_id])) {
                                    $additionalTags[$row->discussion_id] = [];
                                }
                                $additionalTags[$row->discussion_id][] = $row->name;
                            }
                        }
                        
                        $supplemented = 0;
                        foreach ($additional as $discussion) {
                            if ($supplemented >= $needMore) {
                                break;
                            }
                            
                            $hasKeyword = false;
                            
                            if ($filterMode === 'tags') {
                                // ✅ 标签过滤模式
                                $tags = $additionalTags[$discussion->id] ?? [];
                                foreach ($tags as $tagName) {
                                    foreach ($keywords as $keyword) {
                                        if (mb_strpos($tagName, $keyword) !== false) {
                                            $hasKeyword = true;
                                            break 2;
                                        }
                                    }
                                }
                            } else {
                                // 标题过滤模式
                                if (isset($discussion->title)) {
                                    foreach ($keywords as $keyword) {
                                        if (mb_strpos($discussion->title, $keyword) !== false) {
                                            $hasKeyword = true;
                                            break;
                                        }
                                    }
                                }
                            }
                            
                            // 只补充非关键词帖子
                            if (!$hasKeyword) {
                                // 分离置顶和非置顶
                                if ($discussion->is_sticky) {
                                    $stickyDiscussions[] = $discussion;
                                } else {
                                    $normalDiscussions[] = $discussion;
                                }
                                $supplemented++;
                            }
                        }
                    }
                    
                    // ✅ 新功能3：根据排序模式重新排序
                    if ($sortMode === 'random') {
                        // 随机排序（置顶帖不参与随机）
                        shuffle($normalDiscussions);
                    } else {
                        // 时间倒序（默认）
                        usort($normalDiscussions, function ($a, $b) {
                            return $b->last_posted_at <=> $a->last_posted_at;
                        });
                    }
                    
                    // 置顶帖始终按时间倒序排在最前面
                    usort($stickyDiscussions, function ($a, $b) {
                        return $b->last_posted_at <=> $a->last_posted_at;
                    });
                    
                    // 合并：置顶帖 + 普通帖
                    $finalDiscussions = array_merge($stickyDiscussions, $normalDiscussions);
                    
                    // 转换为 Collection 并截取到目标数量
                    $data = collect($finalDiscussions)->take($targetCount);
                }
            }
        }),

    // ✅ 使用事件监听器在首页场景动态调整查询数量
    (new Extend\Event())
        ->listen(\Flarum\Api\Event\WillGetData::class, AdjustQueryLimit::class),
];