'use strict'
const { VueLoaderPlugin } = require('vue-loader')
const path = require('path');
module.exports = {
    //Ideally, this would be split into a proper dev/prod setup, complete with chunking.  But that would require more effort and fine tuning.
    //Similarly, I would normally include a good chunking and minification setup to speed up loading, but as this thing is tiny it
    //would probably be overkill.  Definitely necessary in a full prod environment, however.

    mode: 'development',
    entry: [
        path.resolve( __dirname,'../index.js')
    ],
    output: {
        path: path.resolve( __dirname,'../../web'),
        publicPath: '/',
        filename: 'js/[name].bundle.js'
    },
    module: {
    rules: [
        {
            test: /\.vue$/,
            loader: 'vue-loader'
        },
        {
            test: /\.css$/i,
            use: ['style-loader', 'css-loader'],
        },
    ]
    },
    plugins: [
        new VueLoaderPlugin()
    ],
    resolve: {
        extensions: ['*', '.vue', '.js', '.json'],
        alias: {
            //The @ allows for simplified referencing in-code when doing imports
            '@': path.resolve( __dirname, '..' ),
            //Vue apparently won't load properly without this
            'vue$': 'vue/dist/vue.esm.js'
        }
    }
}