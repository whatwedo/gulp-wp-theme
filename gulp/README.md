Gulp Build Manual
============

This readme explains how the gulp build system with the tasks for this project work. It should always stay in the Gulp folder. Further explanation to new added or changed tasks should be added to this readme. This way we ensure the documentation and gulp tasks stay together, independ of changes to the root project.

**References**
- [Original Repository](https://github.com/whatwedo/gulp-wp-theme)

## How to use
Simply open console or Terminal in the parent of the gulp folder and run:

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


## Configuration
### Production config

You can override the parameters in ```config-production.js```. These parameters will be used when you compile via `gulp --env production`.
