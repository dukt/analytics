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
import {formatByType} from "@/js/utils";

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
  data() {
    return {
      pluginOptions: null,
    }
  },
  computed: {
    counterValue() {
      if (!this.reportResponse || !this.reportResponse.data || !this.reportResponse.data.report) {
        return null
      }

      const report = this.reportResponse.data.report
      let value = 0

      if (report.totals[0][0]?.value) {
        value = report.totals[0][0].value
      }

      // get type from report instead of hardcoded currency
      const type = report.cols[0].type

      // Return formatted value
      return formatByType(this.localeDefinition, type, value)
    }
  }
}
</script>