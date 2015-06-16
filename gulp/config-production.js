var fs   = require('fs');
var gutil = require('gulp-util');
var packageConfig = require('../package.json');

var dest = './dist/wp-content/themes/' + packageConfig.name;
var src = './src';

// Load base config
var prodConfig = require('./config-development');

// Start making changes for production.
// We recommend to access properties directly as shown below and
// not assigning whole objects.
prodConfig.browserify.debug = false;

prodConfig.stylus.options.cache = false;
prodConfig.stylus.options.compress = true;
prodConfig.stylus.options.sourcemap = false;

prodConfig.browserify.transforms.uglifyify = true; // minifies module with UglifyJS

module.exports = prodConfig;
