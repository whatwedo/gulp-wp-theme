var fs           = require('fs');
var packageConfig = require('../package.json');

var dest = './dist/wp-content/themes/' + packageConfig.name;
var src = './src';

var bower_components = './src/bower_components';
var node_modules = './node_modules';

module.exports = {
  options: {
    version: packageConfig.version
  },
  autoprefixer: [
    'last 2 version',
    'safari 5',
    'ie 9',
    'opera 12.1',
    'ios 6',
    'android 4'
  ],
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
  svg: {
    src: src + "/resources/svg/**",
    dest: dest + "/resources/svg"
  },
  markup: {
    src: src + '/templates/**/*.php',
    dest: dest
  },
  copy: {
    src: [
      src + '/*.*' // Meta files e.g. Screenshot for WordPress Theme Selector
    ],
    dest: dest,
    options: {
      base: src // ensure that all copy tasks keep folder structure
    }
  },
  bump: {
    unreleasedPlaceholder: /## unreleased/ig, // To be replaced in documents with version number
    prereleaseChangelogs: false, // If true, changelog update with prerelease bump
    options: {
      preid : 'beta' // Set the prerelase tag to use
    }
  },
  changelog: {
    src: './CHANGELOG.md',
    dest: dest
  },
  browserify: {
    // Enable source maps
    debug: true,
    transforms: {
      uglifyify: false
    },
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
