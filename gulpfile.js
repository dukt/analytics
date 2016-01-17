var gulp = require('gulp'),
    sass = require('gulp-sass'),
    livereload = require('gulp-livereload'),
    del = require('del'),
    uglify = require('gulp-uglify'),
    rename = require('gulp-rename'),
    concat = require('gulp-concat');

var paths = {
    sass: './resources/sass',
    css: './resources/css',
    js: './resources/js',
    jsCompressed: './resources/js/compressed'
}

/* CSS */

gulp.task('styles', function () {
  return gulp.src(paths.sass+'/*.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(gulp.dest(paths.css));
});


/* JS */

gulp.task('scripts', function() {
    return gulp.src([
        paths.js+'/*.js'
    ])
    .pipe(uglify())
    .pipe(gulp.dest(paths.jsCompressed));
});

/* Clean */

gulp.task('clean', function(cb) {
    del([paths.css, paths.jsCompressed], cb)
});


/* Default Task */

gulp.task('default', ['clean'], function() {
    gulp.start('styles', 'scripts');
});


/* Watch */

gulp.task('watch', function() {

    gulp.watch(paths.sass+'/*.scss', ['styles']);
    gulp.watch(paths.js+'/*.js', ['scripts']);

    livereload.listen();

    gulp.watch([paths.css+'/**', paths.jsCompressed]).on('change', livereload.changed);

});
