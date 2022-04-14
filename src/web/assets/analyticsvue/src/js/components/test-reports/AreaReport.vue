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
                  :chart-options="chartOptions"
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
/* global google */

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
    },
    chartOptions() {
      const chartOptions = new ChartOptions().area(this.selectedPeriod)
      chartOptions.hAxis.ticks = this.generateTicks()
      return chartOptions
    },
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
          const dataTable = responseToDataTable(response.data.chart)
          this.chartData = this.formatDataTable(dataTable)
        });
    },
    generateTicks() {
      var ticks = [];

      for (let i = 0; i < this.reportResponse.data.chart.rows.length; i++) {
        var rowDate = this.reportResponse.data.chart.rows[i][0]
        var tickYear = rowDate.substr(0, 4)
        var tickMonth = rowDate.substr(4, 2) - 1
        var tickDay = rowDate.substr(6, 2)
        var tickDate = new Date(tickYear, tickMonth, tickDay)
        ticks.push(tickDate)
      }

      return ticks
    },

    formatDataTable(dataTable) {
      if (this.reportResponse.data.period === 'year') {
        var dateFormatter = new google.visualization.DateFormat({
          pattern: "MMMM yyyy"
        });

        dateFormatter.format(dataTable, 0);
      }

      return dataTable
    }
  }
}
</script>