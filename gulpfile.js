'use strict';

var gulp         = require('gulp'),
    del          = require('del');

gulp.task('copyall', function () {
    gulp.src('./wr-books/**/*')
        .pipe(gulp.dest('../../../Sites/wordpress/wp-content/plugins/wr-books/'));
});

gulp.task('deleteall', function () {
    return del([
        '../../../Sites/wordpress/wp-content/plugins/wr-books/*'], {force:true}
    );
});

gulp.task('syncall', ['deleteall', 'copyall']);