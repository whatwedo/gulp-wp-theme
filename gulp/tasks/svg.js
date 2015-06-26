var changed    = require('gulp-changed');
var svgmin = require('gulp-svgmin');
var config     = require('../config').svg;

module.exports = function(gulp){
  gulp.task('svg', function() {
    return gulp.src(config.src)
      .pipe(changed(config.dest)) // Ignore unchanged files
      .pipe(svgmin()) // Optimize
      .pipe(gulp.dest(config.dest));
  });
};
