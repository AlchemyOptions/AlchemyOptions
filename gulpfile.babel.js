'use strict';

const chalk = require('chalk');
const { watch, parallel, src, dest } = require('gulp');
const logger = require('gulplog');
const rename = require('gulp-rename');
const sass = require('gulp-sass')(require('sass'));
const babel = require('gulp-babel');
const uglify = require('gulp-uglify');

function watchMainStyles() {
    watch(['./styles/**/*.scss']).on('change', path => {
        logger.info(`Watching '${chalk.cyan(path)}'...`);

        return src('./styles/alchemy.scss', { sourcemaps: true })
            .pipe(sass({ outputStyle: 'compressed' }))
            .pipe(rename({ extname: '.min.css' }))
            .pipe(dest('./styles/', { sourcemaps: '.' }));
    });
}

function watchFieldsStyles() {
    watch(['./fields/**/*.scss', '!./fields/**/_*.scss']).on('change', path => {
        logger.info(`Watching '${chalk.cyan(path)}'...`);

        return src(path, { sourcemaps: true })
            .pipe(sass({ outputStyle: 'compressed' }))
            .pipe(rename({ extname: '.min.css' }))
            .pipe(dest('./', { sourcemaps: '.' }));
    });
}

function watchMainScripts() {
    watch(['./scripts/**/*.js', '!./scripts/**/*.min.js']).on('change', path => {
        logger.info(`Watching '${chalk.cyan(path)}'...`);

        return src('./scripts/alchemy.js', { sourcemaps: true })
            .pipe(babel({ presets: ['@babel/env'] }))
            .pipe(uglify())
            .pipe(rename({ extname: '.min.js' }))
            .pipe(dest('./scripts/', { sourcemaps: '.' }));
    });
}

function watchFieldsScripts() {
    watch(['./fields/**/*.js', '!./fields/**/*.min.js']).on('change', path => {
        logger.info(`Watching '${chalk.cyan(path)}'...`);

        return src(path, { sourcemaps: true })
            .pipe(babel({ presets: ['@babel/env'] }))
            .pipe(uglify())
            .pipe(rename({ extname: '.min.js' }))
            .pipe(dest('./', { sourcemaps: '.' }));
    });
}

exports.default = parallel(
    watchMainStyles,
    watchMainScripts,
    watchFieldsStyles,
    watchFieldsScripts
);