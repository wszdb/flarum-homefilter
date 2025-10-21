<?php

namespace Wszdb\HomeFilter\Listener;

use Flarum\Api\Controller\ListDiscussionsController;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Psr\Http\Message\ServerRequestInterface;

class FilterDiscussions
{
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    /**
     * 处理查询过滤
     * 注意：这个方法会被 Flarum 的依赖注入容器调用
     * 参数会自动注入
     */
    public function handle(ListDiscussionsController $controller, Builder $query, ServerRequestInterface $request)
    {
        // 仅在首页（无过滤条件）时应用
        $filterParams = $request->getQueryParams()['filter'] ?? [];
        
        if (empty($filterParams['q'] ?? '') && empty($filterParams['tag'] ?? '')) {
            // 获取配置
            $keywordsStr = $this->settings->get('wszdb-homefilter.keywords', '');
            $limit = (int)$this->settings->get('wszdb-homefilter.limit', 5);

            if (!empty($keywordsStr)) {
                $keywords = array_map('trim', explode(',', $keywordsStr));
                
                // 使用自定义查询逻辑
                $this->applyKeywordFilter($query, $keywords, $limit);
            }
        }
    }

    protected function applyKeywordFilter(Builder $query, array $keywords, int $limit)
    {
        // 构建关键词匹配条件
        $query->where(function ($q) use ($keywords, $limit) {
            // 子查询：统计当前帖子之前有多少个关键词帖子
            $q->whereRaw('(
                SELECT COUNT(*) 
                FROM discussions AS d2 
                WHERE d2.last_posted_at >= discussions.last_posted_at
                AND d2.id != discussions.id
                AND (' . $this->buildKeywordConditions($keywords, 'd2') . ')
            ) < ?', [$limit])
            // 或者当前帖子不是关键词帖子
            ->orWhereRaw('NOT (' . $this->buildKeywordConditions($keywords, 'discussions') . ')');
        });
    }

    /**
     * 构建关键词匹配SQL条件
     */
    protected function buildKeywordConditions(array $keywords, string $tableAlias = 'discussions'): string
    {
        $conditions = [];
        foreach ($keywords as $keyword) {
            if (!empty($keyword)) {
                $escapedKeyword = addslashes($keyword);
                $conditions[] = "{$tableAlias}.title LIKE '%{$escapedKeyword}%'";
            }
        }
        return empty($conditions) ? '1=0' : implode(' OR ', $conditions);
    }
}
