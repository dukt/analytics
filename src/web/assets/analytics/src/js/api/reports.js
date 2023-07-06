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
  getDimensionsMetrics(sourceId) {
    return axios.get(
      Craft.getActionUrl('analytics/reports/get-dimensions-metrics&sourceId=' + sourceId),
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
  getRealtimeReport({ sourceId }) {
    return axios.post(
      Craft.getActionUrl('analytics/reports/realtime-widget'),
      {
        sourceId,
      },
      {
        headers: {
          'X-CSRF-Token': Craft.csrfTokenValue,
        }
      })
  },
  getEcommerceReport({ sourceId, period }) {
    return axios.post(
      Craft.getActionUrl('analytics/reports/ecommerce-widget'),
      {
        sourceId,
        period
      },
      {
        headers: {
          'X-CSRF-Token': Craft.csrfTokenValue,
        }
      })
  },
}