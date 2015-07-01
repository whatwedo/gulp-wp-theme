// For all available options, see node_modules/gulp-wp-theme/gulp/config-development.js
var gulp = require('gulp');

// Override default configs with
// {
//   dev: {}
//   prod: {}
//   user: {}
// }
require('gulp-wp-theme')(gulp, {
  dev: {
    autoprefixer: [
      'last 2 version',
      'safari 5',
      'ie 9',
      'opera 12.1',
      'ios 6',
      'android 4'
    ]
  }
});
