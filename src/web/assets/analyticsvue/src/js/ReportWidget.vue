<template>
  <div class="da-border da-rounded-md da-p-6">
    <div>
      <div>
        <div
          class="da-font-bold"
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
              <template v-if="chartData">
                <analytics-chart
                  :chart-type="reportCriteria.chart"
                  :chart-data="chartData"
                />
              </template>

              <template v-if="debug">
                <div class="da-relative da-mt-6">
                  <pre class="da-border da-rounded-md da-bg-gray-100 da-w-96 da-h-96 da-overflow-auto">
                    <code class="da-min-w-full da-p-4">{{ reportResponse }}</code>
                  </pre>
                </div>
              </template>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import reportsApi from './api/reports'
import AnalyticsChart from '@/js/components/AnalyticsChart';
import {responseToDataTable} from '@/js/utils'

export default {
  components: {
    AnalyticsChart
  },
  data() {
    return {
      debug: false,
      loading: true,
      reportCriteria: {
        viewId: 1,
        chart: 'area',
        period: 'month',
        options: {
          metric: 'ga:users'
        },
      },
      reportResponse: null,
      chartData: null,
    }
  },
  mounted() {
    this.loading = true

    reportsApi.getReport(this.reportCriteria)
      .then(response => {
        this.loading = false
        this.reportResponse = response
        this.chartData = responseToDataTable(response.data.chart)
      });
  }
}
</script>