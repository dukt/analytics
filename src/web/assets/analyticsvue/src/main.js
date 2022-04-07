/* eslint-disable vue/one-component-per-file */

import Vue from 'vue'
import './css/main.pcss'
import Settings from './js/Settings.vue'

window.AnalyticsVueSettings = Vue.extend({
  render: h => h(Settings),
})
