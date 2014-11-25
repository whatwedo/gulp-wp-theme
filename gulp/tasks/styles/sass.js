var gulp = require('gulp');
var sass = require('gulp-ruby-sass');
var handleErrors = require('../../util/handleErrors');
var config = require('../../config').sass;

/**
 * To use Sass, add the tasks in watch.js and build.js
 */

gulp.task('sass', ['images'], function () {
  return gulp.src(config.src)
    .pipe(sass(config.options))
    .on('error', handleErrors)
    .pipe(gulp.dest(config.dest));
});
