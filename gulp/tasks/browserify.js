/* browserify task
   ---------------
   Bundle javascripty things with browserify!

   If the watch task is running, this uses watchify instead
   of browserify for faster bundling using caching.
*/

var browserify   = require('browserify');
var watchify     = require('watchify');
var bundleLogger = require('../util/bundleLogger');
var config			 = require('../config');
var gulp         = require('gulp');
var handleErrors = require('../util/handleErrors');
var source       = require('vinyl-source-stream');
var argv 				 = require('yargs').argv;
var gulpif 			 = require('gulp-if');
var reload = require('browser-sync').reload;

gulp.task('browserify', function() {
	var src = config.themeSrc + '/resources/javascripts/main.js';
    // Required watchify args
	var dest = {
		dev: config.themeDev + '/resources/javascripts/',
		prod: config.themeProd + '/resources/javascripts/'
	};

	var bundler = browserify({
        // Required watchify args
        cache: {}, packageCache: {}, fullPaths: true,
		// Specify the entry point of your app
		entries: [src],
		// Add file extentions to make optional in your requires
		extensions: ['.coffee', '.hbs']
		//debug: true
	});

	var bundle = function() {
		// Log when bundling starts
		bundleLogger.start();

		return bundler
			// Enable source maps!
			.bundle()
			// Report compile errors
			.on('error', handleErrors)
			// Use vinyl-source-stream to make the
			// stream gulp compatible. Specifiy the
			// desired output filename here.
			.pipe(source('app.js'))
			// Specify the output destination
			.pipe(gulpif(argv.prod, gulp.dest(dest.prod), gulp.dest(dest.dev)))
			.pipe(reload({stream:true, once: true}))
			// Log when bundling completes!
			.on('end', bundleLogger.end);
	};

	if(global.isWatching) {
        bundler = watchify(bundler);
		// Rebundle with watchify on changes.
		bundler.on('update', bundle);
	}

	return bundle();
});
