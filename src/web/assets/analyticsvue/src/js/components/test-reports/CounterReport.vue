<template>
  <div class="da-border da-rounded-md da-p-6">
    <div>
      <template v-if="loading">
        <div>Loading…</div>
      </template>
      <template v-else>
        <div class="da-text-center">
          <div class="da-text-6xl">
            {{ counterData.value }}
          </div>
          <div class="da-mt-2">
            <span class="da-text-blue-600">{{ reportResponse.data.metric }}</span> {{ reportResponse.data.periodLabel }}
          </div>
          <div
            class="da-text-gray-500"
          >
            {{ reportResponse.data.view }}
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
import reportsApi from '../../api/reports'

export default {
  data() {
    return {
      loading: true,
      reportCriteria: {
        viewId: 1,
        chart: 'counter',
        period: 'month',
        options: {
          metric: 'ga:users'
        },
      },
      reportResponse: null,
      counterData: null,
    }
  },
  mounted() {
    this.loading = true

    reportsApi.getReport(this.reportCriteria)
      .then(response => {
        this.loading = false
        this.reportResponse = response
        this.counterData = response.data.counter
      });
  }
}
</script>