'use strict';
var replace       = require('gulp-replace');

module.exports = function(gulp, config){
  gulp.task('markup', function() {
    return gulp.src(config.markup.src)
    .pipe(replace(/{PKG_VERSION}/g, config.options.version))
    .pipe(gulp.dest(config.markup.dest));
  });
};
