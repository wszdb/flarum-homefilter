<?php

namespace Wszdb\HomeFilter\Listener;

use Flarum\Api\Controller\ListDiscussionsController;
use Flarum\Api\Event\WillSerializeData;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Facades\Log;

class ProcessDiscussionData
{
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function handle(WillSerializeData $event)
    {
        // 只处理讨论列表
        if (!($event->controller instanceof ListDiscussionsController)) {
            return;
        }

        // 获取配置
        $keywordsStr = $this->settings->get('wszdb-homefilter.keywords', '');
        $limit = (int)$this->settings->get('wszdb-homefilter.limit', 5);

        // 检查是否在首页
        $filterQ = $_GET['filter']['q'] ?? '';
        $filterTag = $_GET['filter']['tag'] ?? '';

        // 记录调试信息
        Log::info('[HomeFilter] 开始处理', [
            'in_homepage' => empty($filterQ) && empty($filterTag),
            'keywords_config' => $keywordsStr,
            'limit_config' => $limit,
            'data_count' => is_array($event->data) ? count($event->data) : 0
        ]);

        // 仅在首页且有配置时过滤
        if (empty($filterQ) && empty($filterTag) && !empty($keywordsStr) && $limit > 0) {
            $keywords = array_filter(array_map('trim', explode(',', $keywordsStr)));

            if (!empty($keywords) && is_array($event->data)) {
                $originalCount = count($event->data);
                $result = [];
                $keywordCount = 0;
                $targetCount = (int)($_GET['page']['limit'] ?? 20);

                // 记录每个帖子的处理情况
                $debugInfo = [];

                foreach ($event->data as $discussion) {
                    // 检查标题是否包含关键词
                    $hasKeyword = false;
                    $matchedKeyword = '';
                    
                    if (isset($discussion->title)) {
                        foreach ($keywords as $keyword) {
                            if (mb_strpos($discussion->title, $keyword) !== false) {
                                $hasKeyword = true;
                                $matchedKeyword = $keyword;
                                break;
                            }
                        }
                    }

                    // 记录调试信息
                    $debugInfo[] = [
                        'title' => $discussion->title ?? 'no title',
                        'has_keyword' => $hasKeyword,
                        'matched' => $matchedKeyword,
                        'keyword_count' => $keywordCount,
                        'action' => ''
                    ];

                    // 根据是否匹配关键词决定是否保留
                    if ($hasKeyword) {
                        if ($keywordCount < $limit) {
                            $result[] = $discussion;
                            $keywordCount++;
                            $debugInfo[count($debugInfo) - 1]['action'] = 'kept (within limit)';
                        } else {
                            $debugInfo[count($debugInfo) - 1]['action'] = 'filtered (exceed limit)';
                        }
                    } else {
                        // 非关键词帖子全部保留
                        $result[] = $discussion;
                        $debugInfo[count($debugInfo) - 1]['action'] = 'kept (no keyword)';
                    }

                    // 如果已经达到目标数量，停止处理
                    if (count($result) >= $targetCount) {
                        break;
                    }
                }

                // 写入详细日志
                Log::info('[HomeFilter] 过滤完成', [
                    'keywords' => $keywordsStr,
                    'limit' => $limit,
                    'original_count' => $originalCount,
                    'filtered_count' => count($result),
                    'keyword_posts' => $keywordCount,
                    'target_count' => $targetCount,
                    'details' => $debugInfo
                ]);

                // 替换数据
                $event->data = $result;
            } else {
                Log::info('[HomeFilter] 跳过过滤', [
                    'keywords_empty' => empty($keywords),
                    'data_is_array' => is_array($event->data)
                ]);
            }
        } else {
            Log::info('[HomeFilter] 不符合过滤条件', [
                'has_filter_q' => !empty($filterQ),
                'has_filter_tag' => !empty($filterTag),
                'keywords_empty' => empty($keywordsStr),
                'limit_zero' => $limit <= 0
            ]);
        }
    }
}