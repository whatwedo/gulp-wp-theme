var gulp          = require('gulp');
var gutil         = require('gulp-util');
var path          = require('path');
var files         = require('../util/files');
var config        = require('../config');
var replace       = require('gulp-replace');
var fs            = require('fs');
var pkg           = JSON.parse(fs.readFileSync('./package.json', 'utf8'));

gulp.task('markup', function() {
  return gulp.src(config.markup.src)
  .pipe(replace(/{PKG_VERSION}/,  pkg.version))
  .pipe(gulp.dest(config.markup.dest));
});
