<template>
  <div>
    <report-header :report-response="reportResponse" />
    <div
      class="da-mt-4"
    >
      <div>
        <template v-if="chartData">
          <analytics-chart
            class="-da-mx-[24px] -da-mb-[24px]"
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
import AnalyticsChart from '@/js/components/AnalyticsChart.vue';
import {responseToDataTable} from '@/js/utils'
import {ChartOptions} from '@/js/ChartOptions';
import ReportHeader from '@/js/components/ReportHeader.vue';

export default {
  components: {
    ReportHeader,
    AnalyticsChart
  },
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
      chartData: null,
    }
  },
  computed: {
    chartOptions() {
      const chartOptions = new ChartOptions().area(this.reportResponse.data.period)
      // chartOptions.hAxis.ticks = this.generateTicks()
      return chartOptions
    },
  },
  mounted() {
    this.parseReportResponse()
  },
  methods: {
    parseReportResponse() {
      this.chartData = responseToDataTable(this.reportResponse.data.chart, this.localeDefinition)
      // this.chartData = this.formatDataTable(dataTable)
    },
    // generateTicks() {
    //   var ticks = [];
    //
    //   for (let i = 0; i < this.reportResponse.data.chart.rows.length; i++) {
    //     var rowDate = this.reportResponse.data.chart.rows[i][0]
    //     var tickYear = rowDate.substr(0, 4)
    //     var tickMonth = rowDate.substr(4, 2) - 1
    //     var tickDay = rowDate.substr(6, 2)
    //     var tickDate = new Date(tickYear, tickMonth, tickDay)
    //     ticks.push(tickDate)
    //   }
    //
    //   return ticks
    // },

    // formatDataTable(dataTable) {
    //   if (this.reportResponse.data.period === 'year') {
    //     var dateFormatter = new google.visualization.DateFormat({
    //       pattern: "MMMM yyyy"
    //     });
    //
    //     dateFormatter.format(dataTable, 0);
    //   }
    //
    //   return dataTable
    // }
  }
}
</script>