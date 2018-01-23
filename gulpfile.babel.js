'use strict';

const productionBuild = process.env.NODE_ENV === 'production';
const gulp = require('gulp');
const pump = require('pump');
const del = require('del');
const plugins = require('gulp-load-plugins')({
    rename: {
        'gulp-rev-replace': 'revReplace'
    }
});
const pathToBuild = 'build';

gulp.task('clean', cb => {
    del([
        `./${pathToBuild}/**`
    ]).then(() => {
        cb();
    });
});

gulp.task('copy', cb => {
    pump([
        gulp.src([
            `./includes/**/*`,
            `./languages/**/*`,
            `./alchemy-options.php`,
            `./autoload.php`,
            `./index.php`,
            `README.md`,
            `LICENSE`
        ], { base: `.` }),
        gulp.dest(`./${pathToBuild}/`)
    ], cb);
});

gulp.task('styles:main', cb => {
    pump([
        gulp.src('./assets/styles/alchemy.scss'),
        plugins.sass({
            outputStyle: 'compressed'
        }),
        plugins.rev(),
        gulp.dest(`./${pathToBuild}/assets/styles/`),
        plugins.rev.manifest(),
        gulp.dest(`./${pathToBuild}/assets/styles/`)
    ], cb);
});

gulp.task('styles', ['styles:main'], cb => {
    pump([
        gulp.src('./assets/vendor/select2/css/select2.css'),
        plugins.sass({
            outputStyle: 'compressed'
        }),
        plugins.rename('select2.min.css'),
        gulp.dest(`./${pathToBuild}/assets/vendor/select2/css`)
    ], cb);
});

gulp.task('unhash', cb => {
    pump([
        gulp.src(`./${pathToBuild}/inc/script-style.php`),
        plugins.replace(new RegExp(`${project}-(.*?).css`, "i"), `${project}.css`),
        plugins.replace(new RegExp(`${project}-(.*?).min.js`, "i"), `${project}.min.js`),
        plugins.replace(new RegExp(`vendor-(.*?).min.js`, "i"), `vendor.min.js`),
        gulp.dest(`./${pathToBuild}/inc/`)
    ], cb)
});

gulp.task('hash:scripts', cb => {
    pump([
        gulp.src([
            `${pathToSource}/scripts/${project}.min.js`,
            `${pathToSource}/scripts/vendor.min.js`
        ]),
        plugins.rev(),
        gulp.dest(`./${pathToBuild}/scripts/`),
        plugins.rev.manifest(),
        gulp.dest(`./${pathToBuild}/scripts/`)
    ], cb);
});

gulp.task('revreplace:styles', cb => {
    const manifest = gulp.src(`./${pathToBuild}/styles/rev-manifest.json`);

    pump([
        gulp.src(`./${pathToBuild}/inc/script-style.php`),
        plugins.revReplace({
            replaceInExtensions: ['.php'],
            manifest: manifest
        }),
        gulp.dest(`./${pathToBuild}/inc`)
    ], cb);
});

gulp.task('revreplace:scripts', cb => {
    const manifest = gulp.src(`./${pathToBuild}/scripts/rev-manifest.json`);

    pump([
        gulp.src(`./${pathToBuild}/inc/script-style.php`),
        plugins.revReplace({
            replaceInExtensions: ['.php'],
            manifest: manifest
        }),
        gulp.dest(`./${pathToBuild}/inc`)
    ], cb);
});

gulp.task('build', plugins.sequence(
    ['clean'],
    'copy',
    'styles'
));