<template>
  <div
    v-if="reportResponse.data"
  >
    <div class="da-text-center">
      <div class="da-text-5xl">
        {{ counterValue }}
      </div>
      <div class="da-mt-2">
        <strong>{{ reportResponse.data.metric }}</strong>
        {{ reportResponse.data.periodLabel }}
      </div>
      <div
        class="da-text-gray-500 da-text-sm da-mt-1"
      >
        {{ reportResponse.data.source }}
      </div>
    </div>
  </div>
</template>

<script>
import {responseToDataTable} from "@/js/utils";

export default {
  props: {
    reportResponse: {
      type: Object,
      required: true
    },
    localeDefinition: {
      type: Object,
      required: true
    }
  },
  computed: {
    counterValue() {
      if (!this.reportResponse || !this.reportResponse.data || !this.reportResponse.data.response) {
        return null
      }

      if (this.reportResponse.data.response.rows.length === 0) {
        return 0
      }

      const response = responseToDataTable(this.reportResponse.data.response, this.localeDefinition)

      const value = response.getFormattedValue(0, 0)

      if (!value) {
        return 0
      }

      return value
    }
  }
}
</script>