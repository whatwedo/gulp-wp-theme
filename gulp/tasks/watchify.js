'use strict';
var browserifyTask = require('./browserify');

module.exports = function(gulp){
  gulp.task('watchify', function(callback) {
    // Start browserify task with devMode === true
    browserifyTask(callback, true);
  });
};
