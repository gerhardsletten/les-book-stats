var webpack = require('webpack')
var autoprefixer = require('autoprefixer')

module.exports = {
  devtool: 'source-map',
  entry: {
    main: [
      'babel-polyfill',
      './src/index.js',
      'webpack-dev-server/client?http://0.0.0.0:8082',
      'webpack/hot/only-dev-server'
    ]
  },
  output: {
    publicPath: 'http://0.0.0.0:8082/',
    filename: '[name].js'
  },
  module: {
    loaders: [
      {
        test: /\.js$/,
        loader: 'react-hot',
        exclude: /node_modules/
      },
      {
        test: /\.json$/,
        loader: 'json-loader'
      },
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
        loader: 'style!css?modules!less!postcss'
      },
      {
        test: /\.(woff|woff2|ttf|eot|svg)(\?\S*)?$/,
        loader: 'file-loader?name=fonts/[name].[ext]'
      }
    ]
  },
  postcss: () => {
    return [ autoprefixer({ browsers: [ 'last 2 versions' ] }) ]
  },
  plugins: [
    new webpack.optimize.OccurenceOrderPlugin(),
    new webpack.NoErrorsPlugin(),
    new webpack.DefinePlugin({
      __DEVELOPMENT__: true
    })
  ]
}
