<template>
  <div class="da-border da-rounded-md da-p-6">
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
                :chart-type="reportResponse.data.type"
                :chart-data="chartData"
                :chart-options="chartOptions"
              />
            </template>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
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
      return new ChartOptions().pie()
    }
  },
  mounted() {
    this.chartData = responseToDataTable(this.reportResponse.data.chart)
  },
}
</script>