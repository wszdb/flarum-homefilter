const { merge } = require('webpack-merge');
const config = require('flarum-webpack-config');
const path = require('path');

const customConfig = {
  entry: {
    admin: './js/src/admin/index.js',
    forum: './js/src/forum/index.js'
  },
  output: {
    filename: '[name].js',
    path: __dirname + '/js/dist'
  },
  resolve: {
    alias: {
      'flarum/forum': path.resolve(__dirname, '../../vendor/flarum/forum/src/forum'),
      'flarum/common': path.resolve(__dirname, '../../vendor/flarum/core/src/common')
    },
    extensions: ['.ts', '.js', '.json']
  }
};

module.exports = merge(config(), customConfig);
