install:
	@npm install
	@bower install

watch:
	@node_modules/gulp/bin/gulp.js
	@node_modules/gulp/bin/gulp.js watch

build:
	@node_modules/gulp/bin/gulp.js --env production

compile:
	@node_modules/gulp/bin/gulp.js
