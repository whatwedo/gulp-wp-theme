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

//var requireDir = require('require-dir');
//var gutil = require('gulp-util');
//var glob = require('glob');

// Require all tasks in gulp/tasks, including subfolders
module.exports = function(gulp, config){
  // Create final config for this build
  config = require('./gulp/config')(config);

  require('./gulp/tasks/build')(gulp, config);
  require('./gulp/tasks/default')(gulp, config);
  require('./gulp/tasks/browserify')(gulp, config);
  require('./gulp/tasks/browserSync.js')(gulp, config);
  require('./gulp/tasks/bump')(gulp, config);
  require('./gulp/tasks/changelog')(gulp, config);
  require('./gulp/tasks/copy')(gulp, config);
  require('./gulp/tasks/images')(gulp, config);
  require('./gulp/tasks/markup')(gulp, config);
  require('./gulp/tasks/svg')(gulp, config);
  require('./gulp/tasks/watch')(gulp, config);
  require('./gulp/tasks/watchify')(gulp, config);
  require('./gulp/tasks/styles/stylus')(gulp, config);

  //gutil.log(gulp);
  return gulp;
};
