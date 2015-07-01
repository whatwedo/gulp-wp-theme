'use strict';

var bump         = require('gulp-bump');
var prompt       = require('gulp-prompt');
var handleErrors = require('../util/handleErrors');
var semver       = require('semver');
var replace      = require('gulp-replace');

module.exports = function(gulp, config){
  gulp.task('bump', function(callback) {
    gulp.src('./*')
    .pipe(prompt.prompt({
      type: 'checkbox',
      name: 'bump',
      message: 'What type of bump would you like to do?',
      choices: ['patch', 'minor', 'major', 'prerelease']
    }, function(res){

      // get new version
      var newVer = semver.inc(config.options.version, res.bump[0]);

      // format date
      var date = new Date();
      var yyyy = date.getFullYear().toString();
      var mm = (date.getMonth()+1).toString(); // getMonth() is zero-based
      var dd  = date.getDate().toString();
      var dateHumanReadable = yyyy + '-' + (mm[1]?mm:"0"+mm[0]) + '-' + (dd[1]?dd:"0"+dd[0]);
      var waitCounter = 0;
      var endTrigger = function() { // function to trigger build asap all bumping is done
        waitCounter++;
        if (waitCounter == 2) {
          gulp.start('build');
        }
      };

      // replace version in json files
      gulp.src(['./bower.json', './package.json'])
      .pipe(bump({
        version: newVer
      }))
      .pipe(gulp.dest('./'))
      .on('error', handleErrors)
      .on('end', endTrigger);

      // replace version in CHANGELOG
      gulp.src(['./CHANGELOG.md'])
      .pipe(replace(config.bump.options.unreleasedPlaceholder, '## v' + newVer + ' - ' + dateHumanReadable))
      .pipe(gulp.dest('./'))
      .on('error', handleErrors)
      .on('end', endTrigger);

      callback();

    }));
  });
};
