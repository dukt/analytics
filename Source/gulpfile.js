var gulp = require('gulp'),
    less = require('gulp-less'),
    livereload = require('gulp-livereload'),
    del = require('del');

var paths = {
    less: './analytics/resources/less',
    css: './analytics/resources/css'
}

gulp.task('less', function () {
  return gulp.src(paths.less+'/**/*.less')
    .pipe(less({
      paths: []
    }))
    .pipe(gulp.dest(paths.css));
});

gulp.task('clean', function(cb) {
    del([paths.css], cb)
});

gulp.task('default', ['clean'], function() {
    gulp.start('less');
});


gulp.task('watch', function() {

    gulp.watch(paths.less+'/**/*.less', ['less']);

    livereload.listen();

    gulp.watch([paths.css+'/**']).on('change', livereload.changed);

});