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
                  :chart-options="chartOptions"
                />
              </template>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import reportsApi from '../../api/reports'
import AnalyticsChart from '@/js/components/AnalyticsChart';
import {responseToDataTable} from '@/js/utils'
import {ChartOptions} from '@/js/ChartOptions';

export default {
  components: {
    AnalyticsChart
  },
  data() {
    return {
      loading: true,
      reportCriteria: {
        viewId: 1,
        chart: 'table',
        period: 'month',
        options: {
          dimension: 'ga:country',
          metric: 'ga:users'
        },
      },
      reportResponse: null,
      chartData: null,
    }
  },
  computed: {
    chartOptions() {
      return new ChartOptions().table()
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