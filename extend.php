<?php

namespace Wszdb\HomeFilter;

use Flarum\Extend;
use Flarum\Api\Controller\ListDiscussionsController;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\Discussion\Discussion;
use Flarum\Database\AbstractModel;
use Illuminate\Database\ConnectionInterface;
use Wszdb\HomeFilter\Listener\AdjustQueryLimit;

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
        }),

    // ✅ 使用原版的 prepareDataForSerialization 方式
    (new Extend\ApiController(ListDiscussionsController::class))
        ->prepareDataForSerialization(function ($controller, &$data, $request, $document) {
            $settings = resolve(SettingsRepositoryInterface::class);
            
            // 获取配置
            $keywordsStr = $settings->get('wszdb-homefilter.keywords', '');
            $limit = (int)$settings->get('wszdb-homefilter.limit', 5);
            $filterMode = $settings->get('wszdb-homefilter.filter_mode', 'title');
            
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
                    
                    // ✅ 如果是标签过滤模式，预先查询所有帖子的标签
                    $discussionTags = [];
                    if ($filterMode === 'tags') {
                        $discussionIds = $data->pluck('id')->toArray();
                        if (!empty($discussionIds)) {
                            // ✅ 使用 resolve 获取数据库连接，而不是 DB Facade
                            $db = resolve(ConnectionInterface::class);
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
                    
                    // 第一步：标记要删除的项
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
                            } else {
                                $toRemove[] = $index;
                                $filteredIds[] = $discussion->id;
                            }
                        } else {
                            $keptIds[] = $discussion->id;
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
                        $actor = $request->getAttribute('actor');
                        $allExcludedIds = array_merge($keptIds, $filteredIds);
                        
                        // 查询额外的帖子
                        $additional = Discussion::query()
                            ->whereVisibleTo($actor)
                            ->whereNotIn('id', $allExcludedIds)
                            ->orderBy('last_posted_at', 'desc')
                            ->limit($needMore * 2)
                            ->get();
                        
                        // ✅ 如果是标签模式，查询补充帖子的标签
                        $additionalTags = [];
                        if ($filterMode === 'tags' && $additional->count() > 0) {
                            $additionalIds = $additional->pluck('id')->toArray();
                            
                            // ✅ 使用 resolve 获取数据库连接
                            $db = resolve(ConnectionInterface::class);
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
                                $data->push($discussion);
                                $supplemented++;
                            }
                        }
                    }
                }
            }
        }),

    // ✅ 使用事件监听器在首页场景动态调整查询数量
    (new Extend\Event())
        ->listen(\Flarum\Api\Event\WillGetData::class, AdjustQueryLimit::class),
];
