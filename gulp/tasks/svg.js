'use strict';
var changed    = require('gulp-changed');
var svgmin = require('gulp-svgmin');

module.exports = function(gulp, config){
  gulp.task('svg', function() {
    return gulp.src(config.svg.src)
    .pipe(changed(config.svg.dest)) // Ignore unchanged files
    .pipe(svgmin()) // Optimize
    .pipe(gulp.dest(config.svg.dest));
  });
};
