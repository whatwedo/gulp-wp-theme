var config        = require('../config');
var markdown      = require('gulp-markdown');
var handleErrors  = require('../util/handleErrors');

module.exports = function(gulp){
  gulp.task('changelog', function() {
      return gulp.src(config.changelog.src)
          .pipe(markdown())
          .pipe(gulp.dest(config.changelog.dest))
          .on('error', handleErrors);
  });
};
