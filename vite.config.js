import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue2'
import * as path from "path";
import {viteExternalsPlugin} from 'vite-plugin-externals';
import viteEslintPlugin from 'vite-plugin-eslint';

export default defineConfig(({command, mode}) => {
  process.env = {...process.env, ...loadEnv(mode, process.cwd(), '')};
  const port = process.env.DEV_PORT || 3000;
  return {
    base: command === 'serve' ? '' : '/dist/',
    // publicDir: './src/web/assets/analytics/dist',
    root: "./src/web/assets/analytics/",
    build: {
      emptyOutDir: true,
      manifest: true,
      sourcemap: true,
      // outDir: './src/web/assets/analytics/dist/',
      rollupOptions: {
        input: './src/web/assets/analytics/src/main.js',
      },
    },
    // define: {
    //   __VUE_OPTIONS_API__: true,
    //   __VUE_PROD_DEVTOOLS__: false,
    // },
    resolve: {
      alias: {
        '@': path.resolve('./src/web/assets/analytics/src/'),
      },
      // extensions: ['.vue', '.js']
    },
    plugins: [
      vue(),
      viteExternalsPlugin({
        'vue': 'Vue',
        'vue-router': 'VueRouter',
        'vuex': 'Vuex',
        'axios': 'axios'
      }),
      viteEslintPlugin({
        cache: false,
        fix: true,
      }),
    ],
    server: {
      host: '0.0.0.0',
      port,
    }
  }
})