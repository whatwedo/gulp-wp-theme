/* Notes:
   - gulp/tasks/browserify.js handles js recompiling with watchify
   - gulp/tasks/browserSync.js watches and reloads compiled files
*/

var gulp = require('gulp');
var config = require('../config');
var styles = require('./styles');
var copy = require('./copy');
var images = require('./images');
var reload = require('browser-sync').reload;

// Force page reloading
// Default only inject changes like CSS
gulp.task('bs-reload', function () {
    reload();
});

gulp.task('watch', ['setWatch', 'browserSync'], function() {
  // Compile Tasks
  // Note: The browserify task handles js recompiling with watchify
	gulp.watch(styles.base + '/**', ['styles']);

  // Optimize tasks
	gulp.watch(images.src, ['images']);

  // Copy tasks
	gulp.watch(copy.src, ['copy']);
  gulp.watch(copy.src[2], ['copy-meta']);
	gulp.watch(config.themeDev + '/**/*.php', ['bs-reload']);
});
