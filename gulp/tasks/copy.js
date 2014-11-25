var gulp 				 = require('gulp');
var config			 = require('../config');
var handleErrors = require('../util/handleErrors');

gulp.task('copy-meta', function() {
	var src = config.copy.meta.src;

	var dest = config.copy.meta.dest;

	return gulp.src(src)
	.pipe(gulp.dest(dest))
	.on('error', handleErrors);
});

gulp.task('copy', ['copy-meta']);
