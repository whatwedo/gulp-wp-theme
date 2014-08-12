var browserSync = require('browser-sync');
var gulp        = require('gulp');
var config      = require('../config.js');

gulp.task('browserSync', ['build'], function() {
	var args = {};

	args.proxy = config.localRootUrl || null;

	if(args.proxy){
		browserSync.init(args);
	}
});
