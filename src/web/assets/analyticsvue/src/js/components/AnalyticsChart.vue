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
google.charts.load('current', {
  packages: ['corechart', 'table', 'geochart']
})

import {ChartOptions} from '../ChartOptions'

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
      if (
        this.chartType === 'area' ||
        this.chartType === 'pie' ||
        this.chartType === 'table' ||
        this.chartType === 'geo'
      ) {
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
          this.drawAreaChart()
          break
        }
        case 'pie': {
          this.drawPieChart()
          break
        }
        case 'table': {
          this.drawTableChart()
          break
        }
        case 'geo': {
          this.drawGeoChart()
          break
        }
      }
    },

    drawAreaChart() {
      const chart = new google.visualization.AreaChart(this.$refs.chart)
      const chartOptions = new ChartOptions().area('month')
      chart.draw(this.chartData, chartOptions)
    },
    drawPieChart() {
      const chart = new google.visualization.PieChart(this.$refs.chart)
      const chartOptions = new ChartOptions().pie()
      chart.draw(this.chartData, chartOptions)
    },
    drawTableChart() {
      const chart = new google.visualization.Table(this.$refs.chart)
      const chartOptions = new ChartOptions().table()
      chart.draw(this.chartData, chartOptions)
    },
    drawGeoChart() {
      const chart = new google.visualization.GeoChart(this.$refs.chart)
      const chartOptions = new ChartOptions().geo('ga:continent')
      chart.draw(this.chartData, chartOptions)
    },
  },
}
</script>