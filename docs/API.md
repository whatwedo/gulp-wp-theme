# gulp-wp-theme API docs

## require('gulp-wp-theme')(gulp[, configuration])
Add the tasks from gulp-wp-theme to the given gulp instance.

### gulp
Type: `Object`

A gulp instance. Create one with `var gulp = require('gulp');`.

### configuration
Type: `Object`

Configuration passing to the tasks of gulp-wp-theme.

```js
var gulp = require('gulp');

require('gulp-wp-theme')(gulp, {
  dev: {
    autoprefixer: [
      'last 2 version',
      'safari 5',
      'ie 9',
      'opera 12.1',
      'ios 6',
      'android 4'
    ]
  },
  prod: {
  	browserify: {
  		transforms: {
  			uglifyify: false
  		}
  	}
  }
});
```

They are getting merged with the default configuration parameters. Take a look at the [config-development.js](https://github.com/whatwedo/gulp-wp-theme/blob/master/gulp/config-development.js) and find out about the possible configuration parameters. You can also check out the [config-production.js](https://github.com/whatwedo/gulp-wp-theme/blob/master/gulp/config-production.js) to see what we configured per default for the production build.
