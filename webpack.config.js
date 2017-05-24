var path = require('path');
var webpack = require('webpack');

module.exports = {
    entry: './assets/scripts/alchemy.js',
    output: {
        path: path.resolve(__dirname, './assets/scripts'),
        filename: 'alchemy.min.js'
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
            }
        }),
        new webpack.SourceMapDevToolPlugin({
            filename: 'alchemy.js.map'
        }),
        new webpack.ProvidePlugin({
            $: 'jQuery',
            jQuery: 'jQuery'
        })
    ]
};