var gulp = require('gulp');

gulp.task('build', ['browserify', 'stylus', 'images', 'svg', 'markup', 'copy', 'changelog']);
