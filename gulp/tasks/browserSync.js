'use strict';
var browserSync = require('browser-sync');

module.exports = function(gulp, config){
  gulp.task('browserSync', ['build'], function() {
    browserSync(config.browserSync);
  });
};
