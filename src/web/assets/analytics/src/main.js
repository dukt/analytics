/* eslint-disable vue/one-component-per-file */

import Vue from 'vue'
import './css/main.pcss'
import EcommerceWidget from './js/EcommerceWidget.vue'
import ReportWidget from './js/ReportWidget.vue'
import ReportWidgetSettings from './js/ReportWidgetSettings.vue'
import RealtimeWidget from './js/RealtimeWidget.vue'
import ReportField from './js/ReportField.vue'
import Settings from './js/Settings.vue'
import TestsFormatting from './js/tests/Formatting.vue'


// Ecommerce Widget
window.AnalyticsVueEcommerceWidget = Vue.extend(EcommerceWidget)

// Report Widget
window.AnalyticsVueReportWidget = Vue.extend(ReportWidget)
window.AnalyticsVueReportWidgetSettings = Vue.extend(ReportWidgetSettings)

// Realtime Widget
window.AnalyticsVueRealtimeWidget = Vue.extend(RealtimeWidget)

// Report Field
window.AnalyticsVueReportField = Vue.extend(ReportField)

// Settings
window.AnalyticsVueSettings = Vue.extend(Settings)

// Tests
window.AnalyticsVueTestsFormatting = Vue.extend(TestsFormatting)