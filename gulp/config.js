var fs = require('fs');
var packageJson = require('../package.json');
var gutil = require('gulp-util');

/**
* Configuration
* Will not be 1:1 assembled for use in Gulp files.
* See bottom of this file for the assembled version.
*/
var config = {
  paths: {
    themePath: './wp-content/themes/',
    themeFolder: {
      src: packageJson.name + '-src',
      dev: packageJson.name + '-dev',
      prod: packageJson.name
    }
  }
}


/**
 * You should not make configurations below
 */

var hasUserConfig = fs.existsSync('./gulp/userConfig.js');
var hasSampleConfig = fs.existsSync('./gulp/userConfig-example.js');
var userConfig;

/**
 * Function to merge two configs
 * @type {Dictionary}
 * @return Merge config object
 */
var mergeConfigs = function(obj1, obj2) {
  var obj = {};

  for (var x in obj1)
    if (obj1.hasOwnProperty(x))
      obj[x] = obj1[x];

  for (var x in obj2)
    if (obj2.hasOwnProperty(x))
      obj[x] = obj2[x];

  return obj;
}

/**
 * Assemble Config
 */

config = {
  themeSrc: config.paths.themePath + config.paths.themeFolder.src,
  themeDev: config.paths.themePath + config.paths.themeFolder.dev,
  themeProd: config.paths.themePath + config.paths.themeFolder.prod
}

console.log(hasSampleConfig);
console.log(hasUserConfig);

if(hasUserConfig && hasSampleConfig){
  var userConfigF = require('./userConfig.js')
  var userConfigSampleF = require('./userConfig-example.js')
  userConfig = mergeConfigs(userConfigSampleF, userConfigF);
  gutil.log('Using configuration gulp/userConfig.js');
} else if(hasSampleConfig){
  userConfig = require('./userConfig-example.js')
  gutil.log('Using configuration gulp/userConfig-example.js', gutil.colors.cyan('Make a copy of gulp/userConfig-example.js and rename it to userConfig.js to make your settings.'));
}

if(userConfig){
  config = mergeConfigs(config, userConfig);
}

// Public available for gulp resources
module.exports = config;
