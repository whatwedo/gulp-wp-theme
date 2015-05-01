# Change Log
All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## Unreleased
### Added
- **node and bower shortcut paths for Stylus**: You can now require or import any stylus file from your bower and npm packages with one simple path reference like `@require 'node_modules/normalize.styl/normalize'` and `@require 'bower_components/normalize.styl/normalize'` from any subfolder.

## v0.4.0 - 2015-04-02
### Added
- **User config example file** `config-user-example.js`. It holds a typical configuration we use in our projects at [whatwedo](https://whatwedo.ch). It doesn't do anything as long it's not named `config-user.js`. Further informations were added as comments in the file.
- **Makefile** with shortcuts for the most used commands. Use ```make install``` to install npm and bower packages, ```make compile```to start gulp watcher, ```make build``` to make a production ready build.

### Changed
- Production config: Takes the development config as base now and only replaces single parameters. In addition, the shortcut ```--env prod``` will now also work.
