var fs   = require('fs');
var gutil = require('gulp-util');
var packageConfig = require('../package.json');

var dest = './dist/wp-content/themes/' + packageConfig.name;
var src = './src';

var bower_components = './src/bower_components';
var node_modules = './node_modules';

module.exports = {
  browserSync: {
    server: {
      // We're serving the src folder as well
      // for sass sourcemap linking
      baseDir: [dest]
    },
    open: false,
    files: [
    dest + "/**",
    // Exclude Map files
    "!" + dest + "/**.map"
    ]
  },
  stylus: {
    src: src + "/resources/stylus/**", // files which are watched for changes, but not compiled directly
    main: src + "/resources/stylus/*.{styl, stylus}", // files which are compiled with all their decendants
    dest: dest,
    options: {
      compress: false,
      include: [
        bower_components + '/../', // Shortcut references possible everywhere, e.g. @import 'bower_components/bla'
        node_modules + '/../'      // Shortcut references possible everywhere, e.g. @import 'node_modules/bla'
      ]
    }
  },
  images: {
    src: src + "/resources/images/**",
    dest: dest + "/resources/images"
  },
  substituter: {
    enabled: true,
    cdn: '',
    js: '<script src="{cdn}/{file}"></script>',
    css: '<link rel="stylesheet" href="{cdn}/{file}">'
  },
  markup: {
    src: src + '/templates/**/*.php',
    dest: dest
  },
  copy: {
    // Meta files e.g. Screenshot for WordPress Theme Selector
    meta: {
      src: src + '/*.*',
      dest: dest
    }
  },
  browserify: {
    // Enable source maps
    debug: true,
    // Additional file extentions to make optional
    extensions: ['.coffee', '.hbs'],
    // A separate bundle will be generated for each
    // bundle config in the list below
    bundleConfigs: [{
      entries: src + '/resources/javascripts/index.js',
      dest: dest,
      outputName: 'app.js'
    }/*, {
      entries: './src/javascript/head.coffee',
      dest: dest,
      outputName: 'head.js'
    }*/]
  }
};
