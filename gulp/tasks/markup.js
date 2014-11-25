var gulp = require('gulp');
var gutil = require('gulp-util');
var path = require('path');
var substituter = require('gulp-substituter');
var files = require('../util/files');
var config = require('../config');

if (config.substituter) {
  if (config.substituter.js) {
    var preset = config.substituter.js.replace("{cdn}", config.substituter.cdn);
    config.substituter.js = function() {
      var bundles = []
      config.browserify.bundleConfigs.forEach(function (bundleConfig) {
        bundles.push(path.join(bundleConfig.dest, bundleConfig.outputName));
      });
      var pathWithCdn;
      return files(bundles, function(name) {
        var path = preset.replace("{file}", name);
        return path;
      });
    };
  }

  if (config.substituter.css) {
    var preset = config.substituter.css.replace("{cdn}", config.substituter.cdn);
    config.substituter.css = function() {
      return files(path.join(config.stylus.dest, "*.css"), function(name) {
        var path = preset.replace("{file}", name);
        return path;
      });
    };
  }
}

gulp.task('markup', function() {
  return gulp.src(config.markup.src)
  .pipe(substituter(config.substituter))
  .pipe(gulp.dest(config.markup.dest));
});
