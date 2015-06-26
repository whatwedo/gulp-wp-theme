var config       = require('../config');
var bump         = require('gulp-bump');
var prompt       = require('gulp-prompt');
var handleErrors = require('../util/handleErrors');
var semver       = require('semver');
var replace      = require('gulp-replace');
var fs           = require('fs');
var path         = require('path');

module.exports = function(gulp){
  
  gulp.task('bump', function(callback) {
    var type = 'patch'

    gulp.src('./*')
    .pipe(prompt.prompt({
      type: 'checkbox',
      name: 'bump',
      message: 'What type of bump would you like to do?',
      choices: ['patch', 'minor', 'major', 'prerelease']
    }, function(res){

      // get new version
      var pkg = JSON.parse(fs.readFileSync('./package.json', 'utf8'));
      var newVer = semver.inc(pkg.version, res.bump[0]);

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
      .pipe(replace(/## unreleased/ig, '## v' + newVer + ' - ' + dateHumanReadable))
      .pipe(gulp.dest('./'))
      .on('error', handleErrors)
      .on('end', endTrigger);

      callback();

    }))
  });
};
