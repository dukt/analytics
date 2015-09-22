var gulp = require('gulp'),
    less = require('gulp-less'),
    livereload = require('gulp-livereload'),
    del = require('del'),
    uglify = require('gulp-uglify'),
    rename = require('gulp-rename'),
    concat = require('gulp-concat'),
    bower = require('gulp-bower');

var paths = {
    less: './analytics/resources/less',
    css: './analytics/resources/css',
    js: './analytics/resources/js',
    jsCompressed: './analytics/resources/js/compressed',
    bower: './analytics/resources/lib/',
}

gulp.task('bower', function() {
  return bower()
    .pipe(gulp.dest(paths.bower))
});

/* Less */

gulp.task('less', function () {
  return gulp.src(paths.less+'/**/*.less')
    .pipe(less({
      paths: []
    }))
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
    gulp.start('bower', 'less', 'scripts');
});


/* Watch */

gulp.task('watch', function() {

    gulp.watch(paths.less+'/**/*.less', ['less']);
    gulp.watch(paths.js+'/*.js', ['scripts']);

    livereload.listen();

    gulp.watch([paths.css+'/**', paths.jsCompressed]).on('change', livereload.changed);

});
