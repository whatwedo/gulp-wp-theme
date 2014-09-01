var browserSync = require('browser-sync');
var gulp        = require('gulp');
var config      = require('../config.js');

gulp.task('browserSync', ['build'], function() {
  browserSync({
    files: [
      // Watch everything in build
      config.themeDev,
      "!" + config.themeDev + "/**.map"
    ],
	proxy: config.localRootUrl || null
  });
});
