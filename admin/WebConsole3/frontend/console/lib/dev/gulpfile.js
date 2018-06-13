// Load plugins
var gulp = require('gulp'),
//    sass = require('gulp-ruby-sass'),
    //autoprefixer = require('gulp-autoprefixer'),
    minifycss = require('gulp-minify-css'),
    jshint = require('gulp-jshint'),
    uglify = require('gulp-uglify'),
    //imagemin = require('gulp-imagemin'),
    rename = require('gulp-rename'),
    //clean = require('gulp-clean'),
    concat = require('gulp-concat'),
    notify = require('gulp-notify'),
    cache = require('gulp-cache'),
    livereload = require('gulp-livereload'),
    lr = require('tiny-lr'),
    server = lr();

var src = {
  styl: ['assets/**/*.styl'],
  css: ['assets/**/*.css'],
  coffee: ['assets/**/*.coffee'],
  js: ['assets/**/*.js'],
  bower: ['bower.json', '.bowerrc']
}
src.styles = src.styl.concat(src.css)
src.scripts = src.coffee.concat(src.js)

var publishdir = 'dist'
var dist = {
  all: [publishdir + '/**/*'],
  css: publishdir + '/css/',
  js: publishdir + '/js/',
  vendor: publishdir + '/vendor/'
}    

// Scripts

gulp.task('compress-css', function() {
  return gulp.src(dist.css+"/*.css")
    .pipe(rename({ suffix: '.min' }))
    .pipe(minifycss())
    .pipe(gulp.dest(dist.css))
})
gulp.task('compress-js', function() {
  return gulp.src(dist.js+"/*.js")
    .pipe(rename({ suffix: '.min' }))
    .pipe(uglify())
    .pipe(gulp.dest(dist.js))
})

gulp.task('compress', ['compress-css', 'compress-js'])

gulp.task("build-utils-js",function(){
    return gulp.src('util/**/*.js')
    .pipe(jshint('.jshintrc'))
    .pipe(jshint.reporter('default'))
    .pipe(concat('globunet-util.js'))
    .pipe(gulp.dest(dist.js))
    .pipe(notify({ message: 'Scripts task complete' }));
});

gulp.task("build-utils-css",function(){
    return gulp.src('util/**/*.css')
    .pipe(concat('globunet-util.css'))
    .pipe(gulp.dest(dist.css))
    .pipe(notify({ message: 'Scripts task complete' }));
});



gulp.task('build-utils', function() {
  gulp.run('build-utils-css', 'build-utils-js', 'compress');
});


// Clean
gulp.task('clean', function() {
  return gulp.src(['dist/styles', 'dist/scripts', 'dist/images'], {read: false})
    .pipe(clean());
});

// Default task
gulp.task('default', ['clean'], function() {
    gulp.run('styles', 'scripts', 'images');
});

