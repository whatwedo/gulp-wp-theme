var gulp = require('gulp');
var gutil = require('gulp-util');
var path = require('path');
var files = require('../util/files');
var config = require('../config');

gulp.task('markup', function() {
  return gulp.src(config.markup.src)
  .pipe(gulp.dest(config.markup.dest));
});
