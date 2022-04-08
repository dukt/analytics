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
        <div>
          <div class="da-border da-p-4 da-rounded-md">
            Show <code>{{ reportCriteria.chart }}</code> chart.
            <area-chart />
          </div>
          <div class="da-relative da-mt-6">
            <pre class="da-border da-rounded-md da-bg-gray-100 da-w-96 da-h-96 da-overflow-auto">
              <code class="da-min-w-full da-p-4">{{ reportResponse }}</code>
            </pre>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
import reportsApi from './api/reports'
import AreaChart from '@/js/components/charts/AreaChart';

export default {
  components: {AreaChart},
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