'use strict';
/*
  gulpfile.js
  ===========
  Rather than manage one giant configuration file responsible
  for creating multiple tasks, each task has been broken out into
  its own file in gulp/tasks. Any files in that directory get
  automatically required below.

  To add a new task, simply add a new task file that directory.
  gulp/tasks/default.js specifies the default set of tasks to run
  when you run `gulp`.
*/

var requireDir = require('require-dir');
var gutil = require('gulp-util');

// Require all tasks in gulp/tasks, including subfolders
module.exports = function(gulp, config){
  gutil.log(requireDir('./gulp/tasks', { recurse: true }));

  require('./gulp/tasks/build')(gulp);
  require('./gulp/tasks/default')(gulp);
  require('./gulp/tasks/browserify')(gulp);
  require('./gulp/tasks/browserSync.js')(gulp);
  require('./gulp/tasks/bump')(gulp);
  require('./gulp/tasks/changelog')(gulp);
  require('./gulp/tasks/copy')(gulp);
  require('./gulp/tasks/images')(gulp);
  require('./gulp/tasks/markup')(gulp);
  require('./gulp/tasks/svg')(gulp);
  require('./gulp/tasks/watch')(gulp);
  require('./gulp/tasks/watchify')(gulp);
  require('./gulp/tasks/styles/stylus')(gulp);

  //gutil.log(gulp);
  return gulp;
};
