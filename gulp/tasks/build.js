'use strict';

module.exports = function(gulp){
  gulp.task('build', ['browserify', 'stylus', 'images', 'svg', 'markup', 'copy', 'changelog']);
};
