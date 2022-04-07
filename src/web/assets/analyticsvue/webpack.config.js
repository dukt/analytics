const path = require('path');
const {VueLoaderPlugin} = require('vue-loader')
const MiniCssExtractPlugin = require("mini-css-extract-plugin")
const {CleanWebpackPlugin} = require('clean-webpack-plugin')
const ESLintPlugin = require('eslint-webpack-plugin');
const postcssConfig = path.resolve(__dirname, "postcss.config.js")

const getDevServer = () => {
  const port = process.env.DEV_SERVER_PORT || 8090
  return {
    headers: {"Access-Control-Allow-Origin": "*"},
    port,
    server: 'https',
    allowedHosts: "all",
    host: 'localhost',
  }
}

const getConfig = () => {
  const isDevServerRunning = process.argv[1] ? path.basename(process.argv[1]) === 'webpack-dev-server' : false

  const config = {
    mode: 'development',
    entry: './src/main.js',
    output: {
      filename: 'main.js',
      path: path.resolve(__dirname, 'dist'),
    },
    resolve: {
      alias: {
        '@': path.resolve(__dirname, 'src'),
      },
      extensions: ['.vue', '.js']
    },
    devServer: getDevServer(),
    externals: {
      'vue': 'Vue',
      'vue-router': 'VueRouter',
      'vuex': 'Vuex',
      'axios': 'axios'
    },
    module: {
      rules: [
        {
          test: /\.vue$/,
          loader: 'vue-loader'
        },
        {
          test: /\.js$/,
          use: ['babel-loader'],
        },
        {
          test: /\.(css|pcss)$/,
          use: [
            "vue-style-loader",
            {
              loader: MiniCssExtractPlugin.loader,
              options: {
                esModule: false,
              }
            },
            'css-loader',
            {
              loader: "postcss-loader",
              options: {
                postcssOptions: {
                  config: postcssConfig,
                },
              },
            },
          ]
        },
      ],
    },
    plugins: [
      new VueLoaderPlugin(),
      new MiniCssExtractPlugin({
        filename: "css/[name].css",
        chunkFilename: "css/[name].css",
      }),
      new ESLintPlugin({
        extensions: ['.js', '.vue'],
      }),
    ],
  }

  if (!isDevServerRunning) {
    config.plugins.push(new CleanWebpackPlugin())
  }

  return config
}

module.exports = {
  ...getConfig(),
}