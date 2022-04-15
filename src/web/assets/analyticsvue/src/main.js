/* eslint-disable vue/one-component-per-file */

import Vue from 'vue'
import './css/main.pcss'
import ReportWidget from './js/tests/ReportWidget.vue'
import ReportWidgetSettings from './js/tests/ReportWidgetSettings.vue'
import Settings from './js/Settings.vue'
import Tests from './js/tests/Tests.vue'
import TestReports from './js/tests/TestReports.vue'

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
  data() {
    return {
      selectedReportingView: null,
      selectedChart: 'area',
      selectedPeriod: 'week',
      selectedMetric: null,
    }
  },
  render: h => h(Tests),
})

window.AnalyticsVueTestReports = Vue.extend({
  render: h => h(TestReports),
})
