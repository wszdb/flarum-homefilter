import app from 'flarum/admin/app';

app.initializers.add('wszdb/flarum-homefilter', () => {
  console.log('[HomeFilter] Admin initializer loaded');
  console.log('[HomeFilter] Translator available:', !!app.translator);
  
  // 测试直接输出翻译
  const testKey = 'wszdb-homefilter.admin.keywords_label';
  const translated = app.translator.trans(testKey);
  console.log('[HomeFilter] Translation test:', testKey, '=>', translated);
  
  // 检查语言数据
  console.log('[HomeFilter] Locale data:', app.data.locale);
  console.log('[HomeFilter] Translations:', app.translator.translations);
  
  app.extensionData
    .for('wszdb-homefilter')
    .registerSetting({
      setting: 'wszdb-homefilter.keywords',
      type: 'text',
      label: app.translator.trans('wszdb-homefilter.admin.keywords_label'),
      help: app.translator.trans('wszdb-homefilter.admin.keywords_help'),
      placeholder: app.translator.trans('wszdb-homefilter.admin.keywords_placeholder')
    })
    .registerSetting({
      setting: 'wszdb-homefilter.limit',
      type: 'number',
      label: app.translator.trans('wszdb-homefilter.admin.limit_label'),
      help: app.translator.trans('wszdb-homefilter.admin.limit_help'),
      placeholder: '5',
      min: 1,
      max: 50
    });
  
  console.log('[HomeFilter] Settings registered');
});