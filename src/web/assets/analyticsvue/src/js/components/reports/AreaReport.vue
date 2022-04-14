<template>
  <div class="da-border da-rounded-md da-p-6">
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
            :chart-type="reportResponse.data.type"
            :chart-data="chartData"
            :chart-options="chartOptions"
          />
        </template>
      </div>
    </div>
  </div>
</template>

<script>
/* global google */

import AnalyticsChart from '@/js/components/AnalyticsChart';
import {responseToDataTable} from '@/js/utils'
import {ChartOptions} from '@/js/ChartOptions';

export default {
  components: {
    AnalyticsChart
  },
  props: {
      reportResponse: {
        type: Object,
        required: true
      }
  },
  data() {
    return {
      chartData: null,
    }
  },
  computed: {
    chartOptions() {
      const chartOptions = new ChartOptions().area(this.reportResponse.data.period)
      chartOptions.hAxis.ticks = this.generateTicks()
      return chartOptions
    },
  },
  mounted() {
    this.parseReportResponse()
  },
  methods: {
    parseReportResponse() {
      const dataTable = responseToDataTable(this.reportResponse.data.chart)
      this.chartData = this.formatDataTable(dataTable)
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