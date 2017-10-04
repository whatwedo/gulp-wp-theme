![unmaintained](http://img.shields.io/badge/status-unmaintained-red.png)

Currently unmaintained because we're not doing less frontend work than before.

---


<p align="center">
  <a href="http://gulpjs.com">
    <img height="257" width="114" src="https://raw.githubusercontent.com/whatwedo/gulp-wp-theme/master/artwork/gulp-wp-theme-2x.png">
  </a>
</p>


# gulp-wp-theme

gulp-wp-theme, namely **gulp Wordpress Theme**, is a build system made with gulp for programming and compiling WordPress Themes using browserify and CSS precompilers.

It includes the following tools, tasks, and workflows:

- [Browserify](http://browserify.org/) (with [browserify-shim](https://github.com/thlorenz/browserify-shim)) – Make modularized JavaScript in a node like manner and output different bundles.
- [Watchify](https://github.com/substack/watchify) – caching version of browserify for super fast rebuilds)
- [Stylus](https://learnboost.github.io/stylus/) – CSS precompiler and syntax
- [BrowserSync](http://browsersync.io) – for live reloading and a static server
- A bump task to update the version numbers of your theme.
- A changelog task to pull out a HTML changelog you could use to build into your themes configuration or the WordPress dashboard.
- Image and SVG optimization
- Error Notifications in Notification Center
- Pre configured global WordPress jQuery for use with browserify
- Different configs for development, production and user specific environment

## How to use

Take a look at the [documentation](https://github.com/whatwedo/gulp-wp-theme/blob/master/docs/README.md).


## Contribution

We are open for any contribution to this project as long it's useful in WordPress Theme projects. Our projects are dog food proven, means we use it in our daily projects.
