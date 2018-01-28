'use strict';

const argv = require('yargs').argv;

if( 'build' === argv._[0] && ! argv.ver ) {
    throw new Error('--ver param is required for the build task');
}

const path = require('path');
const fs = require('fs');
const webpack = require('webpack');
const webpackStream = require('webpack-stream');
const gulp = require('gulp');
const pump = require('pump');
const del = require('del');
const plugins = require('gulp-load-plugins')({
    rename: {
        'gulp-rev-replace': 'revReplace'
    }
});
const productionBuild = process.env.NODE_ENV === 'production';
const pathToBuild = 'dist';
const destinationFolder = productionBuild
    ? path.resolve(__dirname, `./${pathToBuild}/alchemy-options/assets/scripts`)
    : path.resolve(__dirname, './assets/scripts');
const webpackConfig = {
    entry: {
        'alchemy': './alchemy.js',
        'alchemy-client': './alchemy-client.js'
    },
    context: path.resolve(__dirname, './assets/scripts'),
    output: {
        path: destinationFolder,
        filename: '[name].min.js'
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                loader: 'babel-loader',
                exclude: /node_modules/
            }
        ]
    },
    performance: {
        hints: false
    },
    resolve: {
        extensions: [".js", ".es6"]
    },
    externals: /^(jQuery|\$|tinymce)$/i,
    plugins: [
        new webpack.optimize.UglifyJsPlugin({
            compress: {
                warnings: false
            },
            sourceMap: true
        }),
        new webpack.ProvidePlugin({
            $: 'jQuery',
            jQuery: 'jQuery'
        })
    ],
    watch: ! productionBuild
};
const replaceInFiles = [
    `./${pathToBuild}/includes/class-options-loader.php`,
    `./${pathToBuild}/includes/class-meta-box.php`
];
const sourceFilesToDelete = [
    `./${pathToBuild}/alchemy-options/assets/styles/rev-manifest.json`,
    `./${pathToBuild}/alchemy-options/assets/scripts/rev-manifest.json`,
    `./${pathToBuild}/alchemy-options/assets/scripts/alchemy.min.js`,
    `./${pathToBuild}/alchemy-options/assets/scripts/alchemy-client.min.js`,
    `./${pathToBuild}/alchemy-options/assets/scripts/alchemy.min.js.map`,
    `./${pathToBuild}/alchemy-options/assets/scripts/alchemy-client.min.js.map`
];
const alchemyVersion = argv.ver;

if (productionBuild) {
    webpackConfig.devtool = '#source-map';
}

gulp.task('clean', cb => {
    del([
        `./${pathToBuild}/**`
    ]).then(() => {
        cb();
    });
});

gulp.task('clean:source', cb => {
    del(sourceFilesToDelete).then(() => {
        cb();
    });
});

gulp.task('clean:build', cb => {
    del([
        `./${pathToBuild}/**`,
        `!./${pathToBuild}`,
        `!./${pathToBuild}/alchemy-options.zip`,
        `!./${pathToBuild}/VERSION`
    ]).then(() => {
        cb();
    });
});

gulp.task('copy', cb => {
    pump([
        gulp.src([
            `./includes/**/*`,
            `./languages/**/*`,
            `./assets/vendor/**/*`,
            `./alchemy-options.php`,
            `./autoload.php`,
            `./index.php`,
            `README.md`,
            `LICENSE`
        ], { base: `.` }),
        gulp.dest(`./${pathToBuild}/alchemy-options/`)
    ], cb);
});

gulp.task('scripts', cb => {
    pump([
        webpackStream(webpackConfig),
        gulp.dest(destinationFolder),
        plugins.rev(),
        gulp.dest(destinationFolder),
        plugins.rev.manifest(),
        gulp.dest(destinationFolder)
    ], cb);
});

gulp.task('styles:main', cb => {
    pump([
        gulp.src('./assets/styles/alchemy.scss'),
        plugins.sass({
            outputStyle: 'compressed'
        }),
        plugins.rev(),
        gulp.dest(`./${pathToBuild}/alchemy-options/assets/styles/`),
        plugins.rev.manifest(),
        gulp.dest(`./${pathToBuild}/alchemy-options/assets/styles/`)
    ], cb);
});

gulp.task('styles:main-watch', cb => {
    pump([
        gulp.src('./assets/styles/alchemy.scss'),
        plugins.sass({
            outputStyle: 'compressed'
        }),
        gulp.dest(`./assets/styles/`)
    ], cb);
});

gulp.task('styles', ['styles:main'], cb => {
    pump([
        gulp.src('./assets/vendor/select2/css/select2.css'),
        plugins.sass({
            outputStyle: 'compressed'
        }),
        plugins.rename('select2.min.css'),
        gulp.dest(`./${pathToBuild}/alchemy-options/assets/vendor/select2/css`)
    ], cb);
});

gulp.task('styles:watch', () => {
    gulp.watch('./assets/styles/**/*.scss', ['styles:main-watch']);
});

gulp.task('version', cb => {
    pump([
        gulp.src(`${pathToBuild}/alchemy-options/alchemy-options.php`),
        plugins.replace('0.0.1', alchemyVersion),
        gulp.dest(`./${pathToBuild}/alchemy-options/`)
    ], cb)
});

gulp.task('add-version', cb => {
    fs.writeFile(`./${pathToBuild}/VERSION`, alchemyVersion, 'utf8', err => {
        if (err) throw err;

        cb();
    });
});

gulp.task('archive', cb => {
    pump([
        gulp.src(`${pathToBuild}/**/*`),
        plugins.zip('alchemy-options.zip'),
        gulp.dest(pathToBuild)
    ], cb);
});

gulp.task('revreplace:styles', cb => {
    const manifest = gulp.src(`./${pathToBuild}/alchemy-options/assets/styles/rev-manifest.json`);

    pump([
        gulp.src(replaceInFiles),
        plugins.revReplace({
            replaceInExtensions: ['.php'],
            manifest: manifest
        }),
        gulp.dest(`./${pathToBuild}/alchemy-options/includes`)
    ], cb);
});

gulp.task('revreplace:scripts', cb => {
    const manifest = gulp.src(`./${pathToBuild}/alchemy-options/assets/scripts/rev-manifest.json`);

    pump([
        gulp.src(replaceInFiles),
        plugins.revReplace({
            replaceInExtensions: ['.php', '.js'],
            manifest: manifest
        }),
        gulp.dest(`./${pathToBuild}/alchemy-options/includes`)
    ], cb);
});

gulp.task('revreplace:maps', cb => {
    const manifest = gulp.src(`./${pathToBuild}/alchemy-options/assets/scripts/rev-manifest.json`);

    pump([
        gulp.src(`${pathToBuild}/alchemy-options/assets/scripts/**/*.min.js`),
        plugins.revReplace({
            replaceInExtensions: ['.js'],
            manifest: manifest
        }),
        gulp.dest(`./${pathToBuild}/alchemy-options/assets/scripts`)
    ], cb);
});

gulp.task('default', plugins.sequence(
    'styles:watch',
    'scripts'
));

gulp.task('build', plugins.sequence(
    ['clean'],
    'copy',
    ['styles', 'scripts', 'version'],
    ['revreplace:styles', 'revreplace:maps'],
    'revreplace:scripts',
    'clean:source',
    'archive',
    'add-version',
    'clean:build'
));