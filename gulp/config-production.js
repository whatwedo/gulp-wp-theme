var fs   = require('fs');
var gutil = require('gulp-util');
var packageConfig = require('../package.json');

var dest = './dist/wp-content/themes/' + packageConfig.name;
var src = './src';

var prodConfig = require('./config-development');

prodConfig.browserify.debug = false;

prodConfig.stylus.options = {
    cache: false,
    compress: true,
    sourcemap: false
};

prodConfig.browserify.transforms = {
  uglifyify: true           // minifies module with UglifyJS
};

module.exports = prodConfig;
