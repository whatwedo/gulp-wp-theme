gulp-wp-theme
============

gulp-wp-theme, namely Gulp Wordpress Theme, is a Gulp + Browserify boilerplate with examples of how to accomplish some common tasks and workflows. Read the [blog post](http://viget.com/extend/gulp-browserify-starter-faq) for more context, and check out the [Wiki](https://github.com/greypants/gulp-starter/wiki) for some good background knowledge.

It was initially started by the [gulp-starter Project](https://github.com/greypants/gulp-starter) and modified by [whatwedo](http://whatwedo.ch) for use with WordPress Themes. Also, all dependencies to Ruby Gem packages were removed.

It includes the following tools, tasks, and workflows:

- [Browserify](http://browserify.org/) (with [browserify-shim](https://github.com/thlorenz/browserify-shim))
- [Watchify](https://github.com/substack/watchify) (caching version of browserify for super fast rebuilds)
- [SASS](http://sass-lang.com/)
- [BrowserSync](http://browsersync.io) for live reloading and a static server
- Image optimization
- Error Notifications in Notification Center
- Non common-js vendor code (like a jQuery plugin)

## How it works

* You specify your themes name in the package.json, for example **my-wp-theme** and rename the theme folder ```gulp-wp-theme-src``` to ```my-wp-theme-src```.
Now when you run gulp, the tasks create a second folder ```my-wp-theme-dev``` with your compiled theme.
* While developing your new theme, you can use ```gulp watch``` for real-time injection of code changes to all your devices and browsers (see [BrowserSync](http://browsersync.io) for further reading).
* When you're ready for production, create a production ready version of your theme with ```gulp --prod```.

## Installation

If you've never used Node or npm before, you'll need to install Node.
If you use homebrew, do:

```
brew install node
```

Otherwise, you can download and install from [here](http://nodejs.org/download/).

### Install Gulp Globally

Gulp must be installed globally in order to use the command line tools. *You may need to use `sudo`*


```
npm install -g gulp
```

Alternatively, you can run the version of gulp installed local to the project instead with


```
./node_modules/.bin/gulp
```

### Install Sass

No need to install a Ruby gem here. We maintain the repository to use only NPM and Bower Packages.
So Sass is going to be installed from NPM.

### Install npm dependencies

```
npm install
```

This runs through all dependencies listed in `package.json` and downloads them
to a `node_modules` folder in your project directory.

## Configuration

* **Rename your theme** in ```packages.json```
* **Rename your theme** in ```.bowerrc```
* **Rename your theme src** folder in ```wp-content/themes``` according to your theme name with the suffix *-src*. For exmaple, if you named your theme in the package.json to ```my-theme```, rename the source folder to ```my-theme-src```.
* **Configure** your local server. Make a Copy of ```userConfig-example.js``` in *gulp/* and name it **```userConfig.js```**.
  * Set ```localRootUrl``` to the URL of your local server. This is need for browserSync, to synchronize your changes to the browser.
* Optionally run ```bower install``` before or after you have configured your vendors.

## Run gulp and be amazed.

```
gulp watch
```


This will run the watcher task defined in `gulp/tasks/watch.js`, which does the following:
- Run 'watch', which has 2 task dependencies, `['setWatch', 'browserSync']`
- `setWatch` sets a variable that tells the browserify task whether or not to use watchify.
- `browserSync` has `build` as a task dependecy, so that all your assets will be processed before browserSync tries to serve them to you in the browser.
