var browserSync = require('browser-sync');
var config      = require('../config').browserSync;

module.exports = function(gulp){
  gulp.task('browserSync', ['build'], function() {
    browserSync(config);
  });
};
