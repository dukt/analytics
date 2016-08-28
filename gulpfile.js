var gulp = require('gulp'),
    sass = require('gulp-sass'),
    livereload = require('gulp-livereload'),
    del = require('del'),
    uglify = require('gulp-uglify'),
    rename = require('gulp-rename'),
    plumber = require('gulp-plumber'),
    concat = require('gulp-concat');

var paths = {
    sass: './resources/sass',
    css: './resources/css',
    js: './resources/js',
    jsCompressed: './resources/js/compressed'
};

var globs = {
    concatJs: [
        paths.js + '/Analytics/Base*.js',
        paths.js + '/Analytics/*.js',
        paths.js + '/Analytics/reports/Base*.js',
        paths.js + '/Analytics/reports/*.js',
    ],

    compressJs: [
        paths.js+'/*.js'
    ],

    watchJs: [
        paths.js+'/*.js',
        paths.js+'/Analytics/*.js',
        paths.js+'/Analytics/**/*.js'
    ],

    watchSass: [
        paths.sass+'/*.scss'
    ],

    watchChange: [
        paths.css+'/**',
        paths.jsCompressed
    ],

    clean: [
        paths.css,
        paths.jsCompressed
    ],
};

var plumberErrorHandler = function(err) {

    notify.onError({
        title: "Garnish",
        message:  "Error: <%= error.message %>",
        sound:    "Beep"
    })(err);

    console.log( 'plumber error!' );

    this.emit('end');
};


/* Tasks */

gulp.task('sass', function () {
  return gulp.src(paths.sass+'/*.scss')
    .pipe(plumber({ errorHandler: plumberErrorHandler }))
    .pipe(sass().on('error', sass.logError))
    .pipe(gulp.dest(paths.css));
});

gulp.task('concatJs', function() {
    return gulp.src(globs.concatJs)
        .pipe(plumber({ errorHandler: plumberErrorHandler }))
        .pipe(concat('Analytics.js'))
        .pipe(gulp.dest( paths.js ));
});

gulp.task('compressJs', ['concatJs'], function() {
    return gulp.src(globs.compressJs)
        .pipe(plumber({ errorHandler: plumberErrorHandler }))
        .pipe(uglify())
        .pipe(gulp.dest(paths.jsCompressed));
});

gulp.task('js', ['concatJs', 'compressJs']);

gulp.task('clean', function(cb) {
    del(globs.clean, cb)
});


gulp.task('build', ['clean'], function() {
    gulp.start('sass', 'js');
});

gulp.task('watch', function() {

    gulp.watch(globs.watchSass, ['sass']);
    gulp.watch(globs.watchJs, ['js']);

    // livereload.listen();

    // gulp.watch(globs.watchChange).on('change', livereload.changed);

});

gulp.task('default', ['build', 'watch']);