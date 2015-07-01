'use strict';

var plumber       = require('gulp-plumber');
var autoprefixer  = require('gulp-autoprefixer');
var stylus        = require('gulp-stylus');
var replace       = require('gulp-replace');
var reload        = require('browser-sync').reload;
var handleErrors  = require('../../util/handleErrors');

module.exports = function(gulp, config){
  gulp.task('stylus', function() {
    // TODO: Move to config
    var minifyOptions = {
      prod: {
        keepSpecialComments: 1
      }
    };

    return gulp.src(config.stylus.main)
    .pipe(plumber())
    .pipe(stylus(config.stylus.options))
    .pipe(gulp.dest(config.stylus.dest))
    .pipe(autoprefixer(config.autoprefixer))
    //.pipe(gulpif(argv.prod, minifycss(minifyOptions.prod)))
    //.pipe(sourcemaps.init({loadMaps: true }))
    //.pipe(sourcemaps.write('.', { includeConent: false,  sourceRoot: '.' }))
    .pipe(replace(/{PKG_VERSION}/g,  config.options.version))
    .pipe(gulp.dest(config.stylus.dest))
    .pipe(reload({
      stream: true
    }))
    .on('error', handleErrors);
  });
};
