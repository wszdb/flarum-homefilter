import app from 'flarum/admin/app';

app.initializers.add('wszdb/flarum-homefilter', () => {
  app.extensionData
    .for('wszdb-homefilter')
    .registerSetting({
      setting: 'wszdb-homefilter.keywords',
      type: 'text',
      label: app.translator.trans('wszdb-flarum-homefilter.admin.keywords_label'),
      help: app.translator.trans('wszdb-flarum-homefilter.admin.keywords_help'),
      placeholder: app.translator.trans('wszdb-flarum-homefilter.admin.keywords_placeholder')
    })
    .registerSetting({
      setting: 'wszdb-homefilter.limit',
      type: 'number',
      label: app.translator.trans('wszdb-flarum-homefilter.admin.limit_label'),
      help: app.translator.trans('wszdb-flarum-homefilter.admin.limit_help'),
      placeholder: '5',
      min: 1,
      max: 50
    });
});