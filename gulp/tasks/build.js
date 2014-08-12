var gulp = require('gulp');

gulp.task('build', ['browserify', 'styles', 'images', 'copy']);
