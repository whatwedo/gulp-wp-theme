'use strict';

var bump         = require('gulp-bump');
var prompt       = require('gulp-prompt');
var handleErrors = require('../util/handleErrors');
var semver       = require('semver');
var replace      = require('gulp-replace');
var gutil        = require('gulp-util');

module.exports = function(gulp, config){
  var bumpOptions = config.bump.options;
  gulp.task('bump', bumpDialogTask);

  function bumpDialogTask(callback) {
    var target = './*'; // project root

    gulp.src(target).pipe(prompt.prompt({
      type: 'list',
      name: 'bump',
      message: 'What type of bump would you like to do?',
      choices: ['patch', 'minor', 'major', 'prerelease']
    }, function(res){
      var selectedChoice = res.bump;
      var newVer = semver.inc(config.options.version, selectedChoice);

      if(selectedChoice === 'prerelease'){
        // Prerelease was chosen
        // Semver increment current
        var recommendedVersion = semver.inc(config.options.version, 'pre', bumpOptions.preid);
        var prereleaseChoices = [
          'Set a new version'
        ];

        if(recommendedVersion){
          // Add recommendation if semver is able to make one
          prereleaseChoices.unshift(recommendedVersion);
        }

        gulp.src(target).pipe(prompt.prompt({
          type: 'list',
          name: 'prerelease',
          message: 'What version will it be?',
          choices: prereleaseChoices
        }, function(res){
          if(res.prerelease === 'Set a new version'){
            // Set explicit prerelease version
            gulp.src(target).pipe(prompt.prompt({
              type: 'input',
              name: 'version',
              message: 'Set a new version e.g. 1.0.0 (will be automatically suffixed with ' + bumpOptions.preid + ')',
            }, function(res){
              newVer = res.version + '-' + bumpOptions.preid + '.0';
              bumpFiles(newVer, callback, true);
            }));
          } else {
            newVer = recommendedVersion;
            bumpFiles(newVer, callback, true);
          }
        }));
      } else {
        bumpFiles(newVer, callback);
      }
    }));
  }

  function bumpFiles(newVer, callback, prerelease){
    var waitCounter = 0;
    var date = new Date();
    var yyyy = date.getFullYear().toString();
    var mm = (date.getMonth()+1).toString(); // getMonth() is zero-based
    var dd  = date.getDate().toString();
    var dateHumanReadable = yyyy + '-' + (mm[1]?mm:"0"+mm[0]) + '-' + (dd[1]?dd:"0"+dd[0]);

    gulp.src(['./bower.json', './package.json'])
    .pipe(bump({
      version: newVer
    }))
    .pipe(gulp.dest('./'))
    .on('error', handleErrors)
    .on('end', function(){
      afterBump(waitCounter);
    });

    gutil.log(prerelease);

    if(!prerelease || (prerelease && config.bump.prereleaseChangelogs)){
      // replace version in CHANGELOG
      gulp.src(['./CHANGELOG.md'])
      .pipe(replace(config.bump.unreleasedPlaceholder, '## v' + newVer + ' - ' + dateHumanReadable))
      .pipe(gulp.dest('./'))
      .on('error', handleErrors)
      .on('end', function(){
        afterBump(waitCounter);
      });
    }

    callback();
  }

  function afterBump(waitCounter){
    waitCounter++;
    if (waitCounter == 2) {
      gulp.start('build');
    }
  }
};
