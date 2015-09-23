'use strict';

module.exports = function(gulp, config){
  var browserifyTask = require('./browserify')(gulp, config);
  gulp.task('watchify', function(callback) {
    // Start browserify task with devMode === true
    browserifyTask(callback, true);
  });
};
