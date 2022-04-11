<template>
  <div class="da-border da-rounded-md da-p-6">
    <div>
      <select
        v-model="selectedPeriod"
        @change="onPeriodChange"
      >
        <option
          v-for="(period, index) in periods"
          :key="index"
          :value="period.value"
        >
          {{ period.label }}
        </option>
      </select>

      {{ selectedPeriod }}
    </div>

    <hr>

    <template v-if="loading">
      <div>Loading…</div>
    </template>
    <template v-else>
      <div>
        <div>
          <div
            class="da-font-bold"
          >
            {{ reportResponse.data.metric }}
          </div>
          <div>{{ reportResponse.data.periodLabel }}</div>
          <div
            class="da-text-gray-500"
          >
            {{ reportResponse.data.view }}
          </div>
          <div
            class="da-mt-4"
          >
            <div>
              <template v-if="chartData">
                <analytics-chart
                  :chart-type="reportCriteria.chart"
                  :chart-data="chartData"
                />
              </template>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import reportsApi from '../../api/reports'
import AnalyticsChart from '@/js/components/AnalyticsChart';
import {responseToDataTable} from '@/js/utils'

export default {
  components: {
    AnalyticsChart
  },
  data() {
    return {
      loading: true,
      reportResponse: null,
      chartData: null,
      periods: [
        {
          value: 'week',
          label: 'Week',
        },
        {
          value: 'month',
          label: 'Month',
        },
        {
          value: 'year',
          label: 'Year',
        },
      ],
      selectedPeriod: 'week',
    }
  },
  computed: {
    reportCriteria() {
      return {
        viewId: 1,
        chart: 'area',
        period: this.selectedPeriod,
        options: {
          metric: 'ga:users'
        },
      }
    }
  },
  mounted() {
    this.getReport()
  },
  methods: {
    onPeriodChange() {
      this.getReport()
    },

    getReport() {
      this.loading = true

      reportsApi.getReport(this.reportCriteria)
        .then(response => {
          this.loading = false
          this.reportResponse = response
          this.chartData = responseToDataTable(response.data.chart)
        });
    }
  }
}
</script>