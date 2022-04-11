/* eslint-disable vue/one-component-per-file */

import Vue from 'vue'
import './css/main.pcss'
import ReportWidget from './js/ReportWidget.vue'
import ReportWidgetSettings from './js/ReportWidgetSettings.vue'
import Settings from './js/Settings.vue'
import Tests from './js/Tests.vue'

window.AnalyticsVueReportWidget = Vue.extend({
  render: h => h(ReportWidget),
})

window.AnalyticsVueReportWidgetSettings = Vue.extend({
  render: h => h(ReportWidgetSettings),
})

window.AnalyticsVueSettings = Vue.extend({
  render: h => h(Settings),
})

window.AnalyticsVueTests = Vue.extend({
  render: h => h(Tests),
})
