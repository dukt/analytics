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
  data () {
    return {
      headings: ['Tiempo', 'Temperatura'],
      // chartData: [
      //   [1,  1000],
      //   [2,  1170],
      //   [3,  660],
      //   [4,  1030]
      // ]
    }
  },
  mounted () {
    // set the library loaded callback here
    google.charts.setOnLoadCallback(() => this.drawChart())
  },
  methods: {
    drawChart () {
      console.log('----draw chart----', this.chartData)
      // const dataTable = google.visualization.arrayToDataTable([
      //   this.headings,
      //   ...this.chartData
      // ], false) // 👈 don't forget "false" here to indicate the first row as labels

      const chart = new google.visualization.LineChart(this.$refs.chart) // 👈 use ref here
      chart.draw(this.chartData, lineChartOptions)
    }
  },
}
</script>