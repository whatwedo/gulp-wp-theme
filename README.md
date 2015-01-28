gulp-wp-theme
============

gulp-wp-theme, namely Gulp Wordpress Theme, is a Gulp + Browserify boilerplate with examples of how to accomplish some common tasks and workflows. Read the [blog post](http://viget.com/extend/gulp-browserify-starter-faq) for more context, and check out the [Wiki](https://github.com/greypants/gulp-starter/wiki) for some good background knowledge.

It was initially started by the [gulp-starter Project](https://github.com/greypants/gulp-starter) and modified by [whatwedo](http://whatwedo.ch) for use with WordPress Themes. Also, all dependencies to Ruby Gem packages were removed.

It includes the following tools, tasks, and workflows:

- [Browserify](http://browserify.org/) (with [browserify-shim](https://github.com/thlorenz/browserify-shim))
- [Watchify](https://github.com/substack/watchify) (caching version of browserify for super fast rebuilds)
- [Stylus](https://learnboost.github.io/stylus/)
- [BrowserSync](http://browsersync.io) for live reloading and a static server
- Image optimization
- Error Notifications in Notification Center
- Pre configured global WordPress jQuery for use with browserify
- Different configs for development, production and user specific environment

## How it works

You specify your themes name in the package.json, for example **my-wp-theme**

Now when you run gulp, the tasks take the content of the `src` folder, compile it and output the result to `dist/wp-content/themes/<yourthemename>`. You can install WordPress to the `dist` folder to develop your theme local.

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

### Install npm dependencies

```
npm install
```

This runs through all dependencies listed in `package.json` and downloads them
to a `node_modules` folder in your project directory.

## Configuration

* **Rename your theme** in ```packages.json```
* **Rename your theme** in ```.bowerrc```
* **Configure** your local server. Make a Copy of ```config-development.js``` in *gulp/* and name it **```config-user.js```**.
  * Override the parameters you need to be different in you local development environment in the config-user.js, delete the other parameters you want to keep default.
* Optionally run ```bower install``` before or after you have configured your vendors.

### Production config

You can override the parameters in ```config-production.js```. These parameters will be used when you compile via `gulp --env production`.

## Run gulp and be amazed.

```
gulp
```


This will run the watcher task defined in `gulp/tasks/watch.js`, which does the following:
- Run 'watch', which has 2 task dependencies, `['setWatch', 'browserSync']`
- `setWatch` sets a variable that tells the browserify task whether or not to use watchify.
- `browserSync` has `build` as a task dependecy, so that all your assets will be processed before browserSync tries to serve them to you in the browser.

### Run before going live

```
gulp --env production
```

You can also use this while development since it starts the watcher with different parameters, but it will minify your scripts and does not output source maps.
