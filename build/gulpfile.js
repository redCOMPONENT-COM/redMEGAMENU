var gulp       	= require('gulp');
var extension  	= require('./package.json');
var config      = require('./gulp-config.json');
var argv       	= require('yargs').argv;
var requireDir 	= require('require-dir');
var zip        	= require('gulp-zip');
var xml2js     	= require('xml2js');
var fs         	= require('fs');
var path       	= require('path');

var parser      = new xml2js.Parser();
var joomlagulp  = requireDir('./node_modules/joomla-gulp', {recurse: true});
var redcoreGulp = requireDir('./build/redCORE/build/gulp-redcore', {recurse: true});
var jgulp       = requireDir('./jgulp', {recurse: true});

// Release
gulp.task('release:mod_redmegamenu', function (cb) {
	var name     = 'mod_' + extension.name;
	var fileName = name;

	if (!argv.skipVersion) {
		fs.readFile('../extensions/modules/' + name + '/' + name + '.xml', function(err, data) {
			parser.parseString(data, function (err, result) {
				fileName += '-v' + result.extension.version[0] + '.zip';

				// We will output where release package is going so it is easier to find
				console.log('Creating new release file in: ' + path.join(config.release_dir + '/modules', fileName));

				return gulp.src('../extensions/modules/' + name + '/**')
					.pipe(zip(fileName))
					.pipe(gulp.dest(config.release_dir + '/modules'));
			});
		});
	}
	else {
		return gulp.src('../extensions/modules/' + name + '/**')
			.pipe(zip(fileName + '.zip'))
			.pipe(gulp.dest(config.release_dir + '/modules'));
	}
});

// Clean
gulp.task('clean:mod_redmegamenu', function() {
	return del(config.wwwDir + '/modules/mod_redmegamenu', {force : true});
});

// Copy
gulp.task('copy:mod_redmegamenu', ['clean:mod_redmegamenu'], function() {
	return gulp.src(extPath + '/**')
		.pipe(gulp.dest(config.wwwDir + '/modules/mod_redmegamenu'));
});

// Watch
gulp.task('watch:mod_redmegamenu', function() {
	gulp.watch(extPath + '/**/*',
		{ interval: config.watchInterval },
		['copy:mod_redmegamenu', browserSync.reload]);
});
