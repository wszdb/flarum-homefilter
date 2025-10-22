<?php

namespace Wszdb\HomeFilter\Listener;

use Flarum\Api\Controller\ListDiscussionsController;
use Flarum\Api\Event\WillGetData;
use Flarum\Settings\SettingsRepositoryInterface;

class AdjustQueryLimit
{
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function handle(WillGetData $event)
    {
        // 只处理讨论列表控制器
        if (!($event->controller instanceof ListDiscussionsController)) {
            return;
        }

        // 获取请求参数
        $request = $event->request;
        $filterParams = $request->getQueryParams()['filter'] ?? [];
        $filterQ = $filterParams['q'] ?? '';
        $filterTag = $filterParams['tag'] ?? '';
        
        // ✅ 关键修复：检测是否是相关讨论查询
        $isRelatedDiscussions = isset($filterParams['nearataRelatedDiscussions']);
        
        // 只在首页场景（无搜索、无标签、非相关讨论）时增加查询数量
        if (empty($filterQ) && empty($filterTag) && !$isRelatedDiscussions) {
            $keywordsStr = $this->settings->get('wszdb-homefilter.keywords', '');
            
            // 只有配置了关键词时才增加查询数量
            if (!empty($keywordsStr)) {
                $originalLimit = $event->limit ?? 20;
                // 增加查询数量以便后续过滤
                $event->limit = $originalLimit * 3;
            }
        }
        // ✅ 对于相关讨论查询，保持默认的 limit，不做任何修改
    }
}
