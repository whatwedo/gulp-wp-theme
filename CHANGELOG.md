# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## v1.0.0 - 2015-07-03
### Added
- **npm support**: gulp-wp-theme is now useable via npm. This makes it easier to
  update in future. *Find an example project in the `example` folder of this
  repository.*
- **Bump placeholder can now be configured** via `config.bump.unreleasedPlaceholder`.
  This makes it possible to use the bump task in any language.
- **autoprefixer can now be configured** via `config.autoprefixer`.

## v0.8.3 - 2015-06-26
### Fixed
- Version replacement works now across all file contents instead of the first match.

## v0.8.2 - 2015-06-16
### Fixed
- Stylus includes in build process work like in compile process. Production config sets variables directly and does no longer assign whole objects. This makes changes in development config less risky.

## v0.8.1 - 2015-05-30
### BREAKING CHANGES
- The **copy** task can now be configured to copy any needed folder or files in the given folder structure of `src`. Previously you had to add new copy tasks to copy.js. Now it's possible to extend the task via configuration, making it ready for updates. **Update your config** as shown in the [initial development config](https://github.com/whatwedo/gulp-wp-theme/blob/v0.8.1/gulp/config-development.js#L49) to get it working with version 0.8.1.

## v0.8.0 - 2015-05-08
### Added
- **SVG cleaning**: You can now add svg files to `resources/svg` and they get cleaned on compile and build.
- **node and bower shortcut paths for Stylus**: You can now require or import any stylus file from your bower and npm packages with one simple path reference like `@require 'node_modules/normalize.styl/normalize'` and `@require 'bower_components/normalize.styl/normalize'` from any subfolder.

### Fixed
- Updated Browserify to get rid of a NPM problem with JSONStream. See [substack/node-browserify#1247](https://github.com/substack/node-browserify/pull/1247) for details.

## v0.7.0 - 2015-04-23
### Added
- `gulp bump` command to increment version in `CHANGELOG.md`, `package.json`, `bower.json` and markup / js / stylus files (version-placeholder: {PKG_VERSION})

## v0.6.0 - 2015-04-22
### Added
- Show changelog in WordPress backend

## v0.5.0 - 2015-04-15
### Added
- Use `make watch` to first start compile tasks, then watcher task.

### Changed
- Changed default task from watching to compiling

### Fixed
- Watcher tasks use kind of different logic for newer browserify versions

### Misc
- *Updated dependency versions*

## v0.4.0 - 2015-04-02
### Added
- **User config example file** `config-user-example.js`. It holds a typical configuration we use in our projects at [whatwedo](https://whatwedo.ch). It doesn't do anything as long it's not named `config-user.js`. Further informations were added as comments in the file.
- **Makefile** with shortcuts for the most used commands. Use ```make install``` to install npm and bower packages, ```make compile```to start gulp watcher, ```make build``` to make a production ready build.

### Changed
- Production config: Takes the development config as base now and only replaces single parameters. In addition, the shortcut ```--env prod``` will now also work.
