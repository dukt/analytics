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
  getReportingViews() {
    return axios.get(
      Craft.getActionUrl('analytics/vue/get-reporting-views'),
      {
        headers: {
          'X-CSRF-Token': Craft.csrfTokenValue,
        }
      })
  }
}