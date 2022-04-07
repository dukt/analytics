/* global Craft */

import axios from 'axios'

export default {
  getAccountExplorerData() {
    return axios.get(Craft.getActionUrl('analytics/settings/get-account-explorer-data'), {
      headers: {
        'X-CSRF-Token': Craft.csrfTokenValue,
      }
    })
  },
}