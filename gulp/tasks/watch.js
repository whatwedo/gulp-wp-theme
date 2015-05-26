/* Notes:
   - gulp/tasks/browserify.js handles js recompiling with watchify
   - gulp/tasks/browserSync.js watches and reloads compiled files
*/

var gulp  = require('gulp');
var config= require('../config');

gulp.task('watch', ['watchify', 'browserSync'], function() {
  gulp.watch(config.stylus.src, ['stylus']);
  gulp.watch(config.changelog.src, ['changelog']);
  gulp.watch(config.images.src, ['images']);
  gulp.watch(config.svg.src, ['svg']);
  gulp.watch(config.markup.src, ['markup']);
  gulp.watch(config.copy.src, ['copy']);

  // Uncomment to use one iof these watchers
  // gulp.watch(config.sass.src,   ['sass']);
});
