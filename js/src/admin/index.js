import app from 'flarum/admin/app';

app.initializers.add('wszdb/flarum-homefilter', () => {
  app.extensionData
    .for('wszdb-homefilter')
    .registerSetting({
      setting: 'wszdb-homefilter.keywords',
      type: 'text',
      label: app.translator.trans('wszdb-homefilter.admin.keywords_label'),
      help: app.translator.trans('wszdb-homefilter.admin.keywords_help'),
    })
    .registerSetting({
      setting: 'wszdb-homefilter.filter_mode',
      type: 'select',
      label: app.translator.trans('wszdb-homefilter.admin.filter_mode_label'),
      help: app.translator.trans('wszdb-homefilter.admin.filter_mode_help'),
      options: {
        title: app.translator.trans('wszdb-homefilter.admin.filter_mode_title'),
        tags: app.translator.trans('wszdb-homefilter.admin.filter_mode_tags'),
      },
      default: 'title',
    })
    .registerSetting({
      setting: 'wszdb-homefilter.limit',
      type: 'number',
      label: app.translator.trans('wszdb-homefilter.admin.limit_label'),
      help: app.translator.trans('wszdb-homefilter.admin.limit_help'),
      min: 0,
      default: 5,
    })
    .registerSetting({
      setting: 'wszdb-homefilter.supplement_mode',
      type: 'select',
      label: app.translator.trans('wszdb-homefilter.admin.supplement_mode_label'),
      help: app.translator.trans('wszdb-homefilter.admin.supplement_mode_help'),
      options: {
        default: app.translator.trans('wszdb-homefilter.admin.supplement_mode_default'),
        unread_random: app.translator.trans('wszdb-homefilter.admin.supplement_mode_unread_random'),
      },
      default: 'default',
    })
    .registerSetting({
      setting: 'wszdb-homefilter.supplement_days',
      type: 'number',
      label: app.translator.trans('wszdb-homefilter.admin.supplement_days_label'),
      help: app.translator.trans('wszdb-homefilter.admin.supplement_days_help'),
      min: 1,
      default: 7,
    })
    .registerSetting({
      setting: 'wszdb-homefilter.sort_mode',
      type: 'select',
      label: app.translator.trans('wszdb-homefilter.admin.sort_mode_label'),
      help: app.translator.trans('wszdb-homefilter.admin.sort_mode_help'),
      options: {
        time_desc: app.translator.trans('wszdb-homefilter.admin.sort_mode_time_desc'),
        random: app.translator.trans('wszdb-homefilter.admin.sort_mode_random'),
      },
      default: 'time_desc',
    });
});