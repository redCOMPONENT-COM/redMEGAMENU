var gulp         = require('gulp');
var extension    = require('./package.json');
var config       = require('./gulp-config.json');
var argv         = require('yargs').argv;
var zip          = require('gulp-zip');
var del          = require('del');
var xml2js       = require('xml2js');
var fs           = require('fs');
var path         = require('path');
var browserSync  = require('browser-sync');
var parser       = new xml2js.Parser();
var defaultTasks = config.defaultTasks;

// Release
gulp.task('release', function (cb) {
	var name     = 'mod_' + extension.name;
	var fileName = name;

	// We will output where release package is going so it is easier to find
	console.log('Creating new release file in: ' + path.join(config.release_dir, fileName + '.zip'));

	if (!argv.skipVersion) {
		fs.readFile('../extensions/modules/' + name + '/' + name + '.xml', function(err, data) {
			parser.parseString(data, function (err, result) {
				fileName += '-v' + result.extension.version[0] + '.zip';
			});
		});
	}

	// Copying module files
	gulp.src('../extensions/modules/' + name + '/**')
		.pipe(gulp.dest('../releases/' + name));

	// Copying module media files
	gulp.src('../build/media/' + name + '/**')
		.pipe(gulp.dest('../releases/' + name + '/media'));

	return gulp.src('../releases/' + name + '/**')
		.pipe(zip(fileName + '.zip'))
		.pipe(gulp.dest(config.release_dir));
});

// Clean
gulp.task('clean', ['clean:mod_redmegamenu:media'], function() {
	return del(config.wwwDir + '/modules/mod_redmegamenu', {force : true});
});

// Clean media
gulp.task('clean:mod_redmegamenu:media', [], function() {
	return del(config.wwwDir + '/media/mod_redmegamenu', {force : true});
});

// Copy
gulp.task('copy', ['clean', 'copy:mod_redmegamenu:media', 'copy:mod_redmegamenu:language'], function() {
	return gulp.src([
		'../extensions/modules/mod_redmegamenu/**',
		'!../extensions/modules/mod_redmegamenu/language',
		'!../extensions/modules/mod_redmegamenu/language/**'
	])
		.pipe(gulp.dest(config.wwwDir + '/modules/mod_redmegamenu'));
});

// Copy media
gulp.task('copy:mod_redmegamenu:media', [], function() {
	return gulp.src('../build/media/mod_redmegamenu/**')
		.pipe(gulp.dest(config.wwwDir + '/media/mod_redmegamenu'));
});

// Copy language
gulp.task('copy:mod_redmegamenu:language', [], function() {
	return gulp.src('../build/media/mod_redmegamenu/language/**')
		.pipe(gulp.dest(config.wwwDir + '/language/'));
});

// Watch
gulp.task('watch', ['watch:mod_redmegamenu:media'], function() {
	gulp.watch('../extensions/modules/mod_redmegamenu/**/*',
		{ interval: config.watchInterval },
		['copy', browserSync.reload]);
});

// Watch media
gulp.task('watch:mod_redmegamenu:media', function() {
	gulp.watch('../build/media/mod_redmegamenu/**/*',
		{ interval: config.watchInterval },
		['copy:mod_redmegamenu:media', browserSync.reload]);
});

// Default task
gulp.task('default', defaultTasks, function() {});
