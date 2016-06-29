var webpack = require('webpack')
var ExtractTextPlugin = require('extract-text-webpack-plugin')
var autoprefixer = require('autoprefixer')

module.exports = {
  entry: {
    bundle: [
      './src/index.js'
    ]
  },
  output: {
    path: './build',
    filename: 'bundle.js'
  },
  module: {
    loaders: [
      {
        test: /\.js$/,
        loader: 'babel-loader',
        query: {
          cacheDirectory: true,
          presets: ['es2015', 'stage-2', 'react'],
          plugins: ['transform-class-properties', 'syntax-class-properties']
        },
        exclude: /node_modules/,
        include: __dirname
      },
      {
        test: /\.less$/,
        loader: ExtractTextPlugin.extract('style', 'css?modules!postcss!less')
      },
      {
        test: /\.(woff|woff2|ttf|eot|svg)(\?\S*)?$/,
        loader: 'file-loader?name=fonts/[name].[hash].[ext]'
      }
    ]
  },
  postcss: function () {
    return [autoprefixer({browsers: ['last 2 versions']})]
  },
  plugins: [
    new ExtractTextPlugin('bundle.css'),
    new webpack.optimize.OccurenceOrderPlugin(),
    new webpack.NoErrorsPlugin(),
    new webpack.DefinePlugin({
      __DEVELOPMENT__: false,
      'process.env': {
        NODE_ENV: JSON.stringify(process.env.NODE_ENV)
      }
    })
  ]
}
