'use strict';

var gulp         = require('gulp'),
    del          = require('del');

gulp.task('copyall', function () {
    gulp.src('./mp_books/**/*')
        .pipe(gulp.dest('../../../Sites/wordpress/wp-content/plugins/mp_books/'));
});

gulp.task('deleteall', function () {
    return del([
        '../../../Sites/wordpress/wp-content/plugins/mp_books/*'], {force:true}
    );
});

gulp.task('syncall', ['deleteall', 'copyall']);