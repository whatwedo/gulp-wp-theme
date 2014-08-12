var changed    = require('gulp-changed');
var gulp       = require('gulp');
var imagemin   = require('gulp-imagemin');
var argv 			 = require('yargs').argv;
var gulpif 		 = require('gulp-if');
var config		 = require('../config');

var imagesConfig = {
	src: [config.themeSrc + '/resources/images/**'],
	dest: {
		dev: config.themeDev + '/resources/images/',
		prod: config.themeProd + '/resources/images/'
	}
};

// Public available for gulp resources
module.exports = imagesConfig;

gulp.task('images', function() {
	var src = imagesConfig.src;
	var dest = imagesConfig.dest;

	return gulp.src(src)
		.pipe(gulpif(argv.prod, changed(dest.prod), changed(dest.dev))) // Ignore unchanged files
		.pipe(imagemin()) // Optimize
		.pipe(gulpif(argv.prod, gulp.dest(dest.prod), gulp.dest(dest.dev)));
});
