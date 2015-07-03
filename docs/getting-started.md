# Using gulp-wp-theme with npm

While you can still download or clone gulp-wp-theme and start your project from stratch, you're also able to install it via npm. This makes updating easier and the tasks are still easy to configure.

## Getting Started

### 1. Install gulp:

If you are not familiar with gulp yet, take a look at the [official gulp documentation](https://github.com/gulpjs/gulp/blob/master/docs/getting-started.md).

### 2. Install gulp-wp-theme in your project devDependencies:

```sh
$ npm install --save-dev gulp-wp-theme
```

### 3. Create a gulpfile.js at the root of your project:

```js
var gulp = require('gulp');

require('gulp-wp-theme')(gulp);
```

### 5. Run gulp:

```sh
$ gulp
```

This runs the default task which compiles in development state with all needed debug information.

To go wild and make your project ready for delivering to production, run gulp with the production parameter.

```sh
$ gulp --env production
```

## Configuration
Next configure the included tasks for your project. Take a look at the [API documentation](https://github.com/whatwedo/gulp-wp-theme/blob/master/docs/API.md).
