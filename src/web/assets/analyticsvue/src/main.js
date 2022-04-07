/* eslint-disable vue/one-component-per-file */

import Vue from 'vue'
import './css/main.pcss'
import Settings from './js/Settings.vue'

new Vue({
  data: {
    message: 'You loaded this page on ' + new Date().toLocaleString()
  },
  render: h => h(Settings)
}).$mount('#analytics-settings')