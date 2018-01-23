var path = require('path');
var webpack = require('webpack');

module.exports = {
    entry: {
        'alchemy': './alchemy.js',
        'alchemy-client': './alchemy-client.js'
    },
    context: path.resolve(__dirname, './assets/scripts'),
    output: {
        path: path.resolve(__dirname, './assets/scripts'),
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
            }
        }),
        new webpack.ProvidePlugin({
            $: 'jQuery',
            jQuery: 'jQuery'
        })
    ]
};

if (process.env.NODE_ENV === 'production') {
    module.exports.devtool = '#source-map';
    module.exports.plugins = (module.exports.plugins || []).concat([
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: '"production"'
            }
        }),
        new webpack.optimize.UglifyJsPlugin({
            sourceMap: true,
            compress: {
                warnings: false
            }
        }),
        new webpack.LoaderOptionsPlugin({
            minimize: true
        })
    ])
}