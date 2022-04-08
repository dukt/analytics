<template>
  <div class="da-border da-p-4 da-rounded-md">
    {{ chartType }}

    <div ref="chart" />
  </div>
</template>

<script>
/* global google */
google.charts.load('current', {'packages':['corechart']})

const lineChartOptions = {
  title: 'Data Line',
  width: '100%',
  height: 250,
  legend: { position: 'bottom' }
}

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
  mounted () {
    // set the library loaded callback here
    google.charts.setOnLoadCallback(() => this.drawChart())
  },
  methods: {
    drawChart () {
      const chart = new google.visualization.LineChart(this.$refs.chart)
      chart.draw(this.chartData, lineChartOptions)
    }
  },
}
</script>