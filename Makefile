install:
	@npm install
	@bower install

compile:
	@node_modules/gulp/bin/gulp.js

build:
	@node_modules/gulp/bin/gulp.js --env production
