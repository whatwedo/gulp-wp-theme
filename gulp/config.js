/**
 * This file checks for different configs and merges them in correct order.
 * There are following configs
 *   - config-development.js: The default config parameters
 *   - config-production.js: Parameters of going live. Minifies and removes debug informations like source maps
 *   - config-user.js: Parameters override the default config-development.js and are for your development
 *                     environment. Don't add this to the repository. It's for you, not your team!
 *
 *  Config overrides: config-production > config-user > config-development
 */

var fs = require('fs');
var gutil = require('gulp-util');
var args = require('yargs').argv;
var gulpif = require('gulp-if');
var extend = require('node.extend');

var config = require('./config-development.js');

var hasUserConfig = fs.existsSync('./gulp/config-user.js');
var hasProductionConfig = fs.existsSync('./gulp/config-production.js');
var isProductionEnv = args.env === 'production';

if (hasUserConfig) {
  var userConfig = require('./config-user.js');
  var mergedUserConfig = extend(true, {}, config, userConfig);
  config = extend(true, {}, mergedUserConfig);
}

if (hasProductionConfig && isProductionEnv) {
  var prodConfig = require('./config-production.js');
  var mergedProdConfig = extend(true, {}, config, prodConfig);
  config = extend(true, {}, mergedProdConfig);
}

module.exports = config;
