'use strict';
var changed    = require('gulp-changed');
var imagemin   = require('gulp-imagemin');

module.exports = function(gulp, config){
  gulp.task('images', function() {
    return gulp.src(config.images.src)
      .pipe(changed(config.images.dest)) // Ignore unchanged files
      .pipe(imagemin()) // Optimize
      .pipe(gulp.dest(config.images.dest));
  });
};
