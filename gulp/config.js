var packageConfig = require('../package.json');

var dest = './dist/wp-content/themes/' + packageConfig.name;
var src = './src';

module.exports = {
  browserSync: {
    server: {
      // We're serving the src folder as well
      // for sass sourcemap linking
      baseDir: [dest, src]
    },
    open: false,
    files: [
    dest + "/**",
    // Exclude Map files
    "!" + dest + "/**.map"
    ]
  },
  /* Example Sass Configuration. Packages have to be installed seperately
  We're currently use Stylus instead, because of Sass' Ruby dependency and
  libsass' not further developed functionality.
  sass: {
    src: src + "/sass/*.{sass, scss}",
    dest: dest,
    options: {
      compass: true,
      bundleExec: true,
      sourcemap: true,
      sourcemapPath: '../sass'
    }
  },*/
  stylus: {
    src: src + "/resources/stylus/*.{styl, stylus}",
    dest: dest,
    options: {
      compress: false,
      sourcemap: {
        inline: true,
        sourceRoot: '.',
        basePath: dest
      }
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
