const path = require('path')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin')
const TerserPlugin = require('terser-webpack-plugin')
const BrowserSyncPlugin = require('browser-sync-webpack-plugin')
const nameProject = 'template-wp'
const JS_DIR = path.resolve(__dirname, 'src')
const BUILD_DIR = path.resolve(__dirname, 'build')

const entry = {
  main: JS_DIR + '/index.js',
}

const output = {
  path: BUILD_DIR,
  filename: 'js/[name].js',
  assetModuleFilename: '[path][name][ext]',
  clean: true,
}
const plugins = (argv) => [
  new MiniCssExtractPlugin({
    filename: 'css/[name].css',
  }),
  new BrowserSyncPlugin(
    {
      host: 'localhost',
      port: 3000,
      proxy: `http://${nameProject}.local/`,
      files: ['**/*.php', 'build/**/*.js', 'build/**/*.css'],
      reloadDelay: 0,
      injectChanges: false,
      notify: false,
    },
    {
      reload: true,
    }
  ),
]

const rules = [
  {
    test: /\.js$/,
    include: [JS_DIR],
    exclude: /node_modules/,
    use: {
      loader: 'babel-loader',
      options: {
        cacheDirectory: true,
        cacheCompression: false,
      },
    },
  },
  {
    test: /\.css$/,
    use: [MiniCssExtractPlugin.loader, 'css-loader'],
  },
  {
    test: /\.scss$/,
    exclude: /node_modules/,
    use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader'],
  },
  {
    test: /\.(png|jpg|svg|jpeg|gif|ico)$/,
    type: 'asset/resource',
  },
]
module.exports = (env, argv) => {
  const isProduction = argv.mode === 'production'
  const publicPath = isProduction ? '../' : '../../'

  return {
    entry: entry,
    output: {
      ...output,
      publicPath: publicPath,
    },
    devtool: isProduction ? 'source-map' : 'eval-cheap-module-source-map',
    plugins: plugins(argv),
    module: {
      rules: rules,
    },
    optimization: {
      minimize: isProduction,
      minimizer: isProduction
        ? [
            new CssMinimizerPlugin(),
            new TerserPlugin({
              terserOptions: {
                compress: {
                  drop_console: true,
                },
              },
            }),
          ]
        : [],
    },
    externals: {
      jquery: 'jQuery',
    },
  }
}
