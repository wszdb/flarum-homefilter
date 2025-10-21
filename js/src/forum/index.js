import app from 'flarum/forum/app';

app.initializers.add('wszdb/flarum-homefilter', () => {
  // 前台暂无需额外逻辑，过滤在后端完成
  console.log('[HomeFilter] Extension loaded');
});
