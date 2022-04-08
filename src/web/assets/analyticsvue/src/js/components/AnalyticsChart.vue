<template>
  <div class="da-border da-p-4 da-rounded-md">
    <template v-if="!isChartTypeSupported">
      Chart type “{{ chartType }}” is not supported.
    </template>

    <div
      ref="chart"
      :class="{
        'da-hidden': !isChartTypeSupported,
      }"
    />
  </div>
</template>

<script>
/* global google */
google.charts.load('current', {'packages':['corechart']})

import {ChartOptions} from '../ChartOptions'

// const lineChartOptions = {
//   title: 'Data Line',
//   width: '100%',
//   height: 250,
//   legend: { position: 'bottom' }
// }

export default {
  props: {
    chartType: {
      type: String,
      default: 'area'
    },
    chartData: {
      type: Array,
      required: true,
    },
  },
  computed: {
    isChartTypeSupported() {
      if (this.chartType === 'area') {
        return true
      }

      return false
    }
  },
  mounted () {
    // set the library loaded callback here
    if (!this.isChartTypeSupported) {
      return null
    }

    google.charts.setOnLoadCallback(() => this.drawChart())
  },
  methods: {
    drawChart () {
      switch(this.chartType) {
        case 'area': {
          const chart = new google.visualization.AreaChart(this.$refs.chart)
          const chartOptions = new ChartOptions().area()
          chart.draw(this.chartData, chartOptions)
          break
        }
      }
    }
  },
}
</script>