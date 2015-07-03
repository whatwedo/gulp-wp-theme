'use strict';

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

var args = require('yargs').argv;
var _ = require('lodash');

var defaultConfigDev = require('./config-development');
var defaultConfigProd = require('./config-production');
var isProductionEnv = args.env === 'production' || args.env === 'prod';

module.exports = function(config){
  var mergedConfig;

  // Create empty configuration container for defaults if no config submitted
  if(!config){
    config = {
      dev: null,
      prod: null,
      user: null
    };
  }

  // Merge submitted configs where needed with defaults
  if(config.dev){
    config.dev = _.merge(defaultConfigDev, config.dev);
  } else {
    config.dev = defaultConfigDev;
  }

  if(config.prod){
    config.prod = _.merge(defaultConfigProd, config.prod);
  } else {
    config.prod = defaultConfigProd;
  }

  // Create concrete config for compilation
  // Take Development Config as a base, start with user config
  var mergedConfig = config.dev;
  if(config.user) {
    mergedConfig = _.merge(mergedConfig, config.user);
  }

  if(isProductionEnv) {
    mergedConfig = _.merge(mergedConfig, config.user);
  }

  return mergedConfig;
};
