var gulp 				 = require('gulp');
var argv 				 = require('yargs').argv;
var gulpif 			 = require('gulp-if');
var config			 = require('../config');
var reload = require('browser-sync').reload;
var handleErrors = require('../util/handleErrors');

var copyConfig = {
	src: [
		config.themeSrc + "/templates/**", // PHP Templates
		config.themeSrc + "/library/**", // PHP helpers
		config.themeSrc + "/*.*" // Meta Files like screenshot.png, favicons, other icons
	],
	dest: {
		dev: config.themeDev,
		prod: config.themeProd
	}
};

// Public available for gulp resources
module.exports = copyConfig;

gulp.task('copy-templates', function() {
	var src = copyConfig.src[0];

	var dest = copyConfig.dest;

	return gulp.src(src)
	.pipe(gulpif(argv.prod, gulp.dest(dest.prod), gulp.dest(dest.dev)))
	.on('error', handleErrors);
});

gulp.task('copy-library', function() {
	var src = copyConfig.src[1];

	var dest = copyConfig.dest;

	return gulp.src(src, {base: config.themeSrc})
	.pipe(gulpif(argv.prod, gulp.dest(dest.prod), gulp.dest(dest.dev)))
	.on('error', handleErrors);
});

gulp.task('copy-meta', function() {
	var src = copyConfig.src[2];

	var dest = copyConfig.dest;

	return gulp.src(src, {base: config.themeSrc})
	.pipe(gulpif(argv.prod, gulp.dest(dest.prod), gulp.dest(dest.dev)))
	.on('error', handleErrors);
});

gulp.task('copy', ['copy-library', 'copy-templates', 'copy-meta']);
