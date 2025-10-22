<?php

namespace Wszdb\HomeFilter;

use Flarum\Extend;
use Flarum\Api\Controller\ListDiscussionsController;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\Discussion\Discussion;
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
        }),

    // ✅ 修复：移除全局 setLimit(100)，改为事件监听
    // 只在首页场景下动态调整查询数量
    (new Extend\ApiController(ListDiscussionsController::class))
        ->prepareDataForSerialization(function ($controller, &$data, $request, $document) {
            $settings = resolve(SettingsRepositoryInterface::class);
            
            // 获取配置
            $keywordsStr = $settings->get('wszdb-homefilter.keywords', '');
            $limit = (int)$settings->get('wszdb-homefilter.limit', 5);
            
            // 检查是否在首页
            $filterParams = $request->getQueryParams()['filter'] ?? [];
            $filterQ = $filterParams['q'] ?? '';
            $filterTag = $filterParams['tag'] ?? '';
            
            // ✅ 新增：严格判断，只在首页且无其他过滤条件时处理
            // 排除相关讨论查询（通过检测 nearataRelatedDiscussions 参数）
            $isRelatedDiscussions = isset($filterParams['nearataRelatedDiscussions']);
            
            if ($isRelatedDiscussions) {
                // 如果是相关讨论查询，直接返回，不做任何处理
                return;
            }
            
            // 仅在首页且有配置时过滤
            if (empty($filterQ) && empty($filterTag) && !empty($keywordsStr) && $limit > 0) {
                $keywords = array_filter(array_map('trim', explode(',', $keywordsStr)));
                
                if (!empty($keywords) && $data->count() > 0) {
                    $targetCount = (int)($request->getQueryParams()['page']['limit'] ?? 20);
                    
                    // 第一步：标记要删除的项
                    $toRemove = [];
                    $keywordCount = 0;
                    $keptIds = [];
                    $filteredIds = [];
                    
                    foreach ($data as $index => $discussion) {
                        // 检查标题是否包含关键词
                        $hasKeyword = false;
                        
                        if (isset($discussion->title)) {
                            foreach ($keywords as $keyword) {
                                if (mb_strpos($discussion->title, $keyword) !== false) {
                                    $hasKeyword = true;
                                    break;
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
                        
                        $supplemented = 0;
                        foreach ($additional as $discussion) {
                            if ($supplemented >= $needMore) {
                                break;
                            }
                            
                            // 检查是否包含关键词
                            $hasKeyword = false;
                            if (isset($discussion->title)) {
                                foreach ($keywords as $keyword) {
                                    if (mb_strpos($discussion->title, $keyword) !== false) {
                                        $hasKeyword = true;
                                        break;
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

    // ✅ 新增：使用事件监听器在首页场景动态调整查询数量
    (new Extend\Event())
        ->listen(\Flarum\Api\Event\WillGetData::class, AdjustQueryLimit::class),
];
