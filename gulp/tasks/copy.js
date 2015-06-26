var config			 = require('../config');
var handleErrors = require('../util/handleErrors');

module.exports = function(gulp){
	gulp.task('copy-all', function() {
		var src = config.copy.src;
		var dest = config.copy.dest;
		var options = config.copy.options;

		return gulp.src(src, options)
		.pipe(gulp.dest(dest))
		.on('error', handleErrors);
	});

	gulp.task('copy', ['copy-all']);
};
