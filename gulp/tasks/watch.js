/* Notes:
   - gulp/tasks/browserify.js handles js recompiling with watchify
   - gulp/tasks/browserSync.js watches and reloads compiled files
*/

var gulp  = require('gulp');
var config= require('../config');gulp

gulp.task('watch', ['setWatch', 'browserSync'], function() {
  gulp.watch(config.stylus.src, ['stylus']);
  gulp.watch(config.images.src, ['images']);
  gulp.watch(config.markup.src, ['markup']);

  // Uncomment to use one iof these watchers
  // gulp.watch(config.sass.src,   ['sass']);
});
