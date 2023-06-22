/* global Craft */

import axios from 'axios'

export default {
  getReport(criteria) {
    return axios.post(
      Craft.getActionUrl('analytics/reports/report-widget'),
      {
        ...criteria
      },
      {
        headers: {
          'X-CSRF-Token': Craft.csrfTokenValue,
        }
      })
  },
  getReportWidgetSettings() {
    return axios.get(
      Craft.getActionUrl('analytics/vue/get-report-widget-settings'),
      {
        headers: {
          'X-CSRF-Token': Craft.csrfTokenValue,
        }
      })
  },
  getDimensionsMetrics(viewId) {
    return axios.get(
      Craft.getActionUrl('analytics/vue/get-dimensions-metrics&viewId=' + viewId),
      {
        headers: {
          'X-CSRF-Token': Craft.csrfTokenValue,
        }
      })
  },
  getElementReport({ elementId, siteId, metric }) {
    return axios.post(
      Craft.getActionUrl('analytics/reports/element'),
      {
        elementId,
        siteId,
        metric,
      },
      {
        headers: {
          'X-CSRF-Token': Craft.csrfTokenValue,
        }
      })
  },
  getRealtimeReport({ viewId }) {
    return axios.post(
      Craft.getActionUrl('analytics/reports/realtime-widget'),
      {
        viewId,
      },
      {
        headers: {
          'X-CSRF-Token': Craft.csrfTokenValue,
        }
      })
  },
  getEcommerceReport({ viewId, period }) {
    return axios.post(
      Craft.getActionUrl('analytics/reports/ecommerce-widget'),
      {
        viewId,
        period
      },
      {
        headers: {
          'X-CSRF-Token': Craft.csrfTokenValue,
        }
      })
  },
}