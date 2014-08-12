var gulp         = require('gulp');
var plumber      = require('gulp-plumber');
var notify       = require('gulp-notify');
var handleErrors = require('../util/handleErrors');
var config = require('../config');
var autoprefixer = require('gulp-autoprefixer');
var sass = require('gulp-sass');
var minifycss = require('gulp-minify-css');
var argv = require('yargs').argv;
var gulpif = require('gulp-if');
var reload = require('browser-sync').reload;

var sassBase = config.themeSrc + "/resources/scss/";
var stylesConfig = {
	base: sassBase,
	src: [sassBase + "*.scss", "!" + sassBase + "_*.scss"],
	dest: {
		dev: config.themeDev,
		prod: config.themeProd
	}
};

// Public available for gulp resources
module.exports = stylesConfig;

gulp.task('styles', function() {
	/**
	* Where to put compiled files
	* @type {Object}
	*/
	var dest = stylesConfig.dest;

	/**
	* Files to compile
	*/
	var src = stylesConfig.src;

	/**
	* Sass Options
	*/
	var options = {
		dev: {
			style: 'expanded'
		},
		prod: {
			style: 'compressed'
		}
	};

	var minifyOptions = {
		prod: {
			keepSpecialComments: 1
		}
	};

	return gulp.src(src)
	.pipe(plumber())
	.pipe(gulpif(argv.prod, sass(options.prod), sass(options.dev)))
	.pipe(gulpif(argv.prod, gulp.dest(dest.prod), gulp.dest(dest.dev)))
	.pipe(autoprefixer('last 2 version', 'safari 5', 'ie 9', 'opera 12.1', 'ios 6', 'android 4'))
	.pipe(gulpif(argv.prod, minifycss(minifyOptions.prod)))
	.pipe(gulpif(argv.prod, gulp.dest(dest.prod), gulp.dest(dest.dev)))
	.pipe(reload({stream:true}))
	//.pipe(livereload(server))
	.on('error', handleErrors);
});
