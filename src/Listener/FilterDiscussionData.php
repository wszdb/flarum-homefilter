<?php

namespace Wszdb\HomeFilter\Listener;

use Flarum\Api\Controller\ListDiscussionsController;
use Flarum\Api\Event\WillGetData;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Facades\Log;

class FilterDiscussionData
{
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function handle(WillGetData $event)
    {
        // 只处理讨论列表
        if (!($event->controller instanceof ListDiscussionsController)) {
            return;
        }

        $filterQ = $_GET['filter']['q'] ?? '';
        $filterTag = $_GET['filter']['tag'] ?? '';

        // 仅在首页时增加查询数量
        if (empty($filterQ) && empty($filterTag)) {
            $limit = $event->limit ?? 20;
            $event->limit = $limit * 3;
            
            Log::info('[HomeFilter] 增加查询数量', [
                'original' => $limit,
                'new' => $limit * 3
            ]);
        }
    }
}
