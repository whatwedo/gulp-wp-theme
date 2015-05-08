# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## Unreleased
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
