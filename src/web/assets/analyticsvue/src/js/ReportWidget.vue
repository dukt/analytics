<template>
  <div class="da-border da-rounded-md da-p-6">
    <div>
      <div
        class="da-font-bold"
      >
        Request
      </div>

      <pre>{{ reportCriteria }}</pre>
    </div>
    <div
      class="da-mt-6 da-font-bold"
    >
      {{ reportCriteria.options.metric }}
    </div>
    <div>{{ reportCriteria.period }}</div>
    <div
      class="da-text-gray-500"
    >
      Site Name
    </div>
    <div
      class="da-mt-4"
    >
      <template v-if="loading">
        <div>Loading…</div>
      </template>
      <template v-else>
        <div class="da-border da-p-4 da-rounded-md da-bg-gray-100 da-h-64 da-overflow-auto">
          <pre>{{ reportResponse }}</pre>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
import reportsApi from './api/reports'

export default {
  data() {
    return {
      loading: false,
      reportCriteria: {
        viewId: 1,
        chart: 'area',
        period: 'week',
        options: {
          metric: 'ga:users'
        },
      },
      reportResponse: null,
    }
  },

  mounted() {
    this.loading = true

    reportsApi.getReport(this.reportCriteria)
      .then(response => {
        this.loading = false
        this.reportResponse = response
      });
  }
}
</script>