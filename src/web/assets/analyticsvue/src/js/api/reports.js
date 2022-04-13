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
  }
}