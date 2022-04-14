<template>
  <div class="da-border da-rounded-md da-p-6">
    <report-header :report-response="reportResponse" />

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
import AnalyticsChart from '@/js/components/AnalyticsChart';
import {responseToDataTable} from '@/js/utils'
import {ChartOptions} from '@/js/ChartOptions';
import ReportHeader from '@/js/components/ReportHeader';

export default {
  components: {
    ReportHeader,
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
      return new ChartOptions().table()
    }
  },
  mounted() {
    this.chartData = responseToDataTable(this.reportResponse.data.chart)
  }
}
</script>