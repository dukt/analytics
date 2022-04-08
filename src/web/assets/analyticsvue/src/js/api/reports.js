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
}