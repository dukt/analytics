<template>
  <div>
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
/* global Analytics */
/* global google */

google.charts.load('current', {
  packages: ['corechart', 'table', 'geochart'],
  language: Analytics.chartLanguage,
  mapsApiKey: Analytics.mapsApiKey,
})

export default {
  props: {
    chartType: {
      type: String,
      default: 'area'
    },
    chartData: {
      type: Array|Object,
      required: true,
    },
    chartScale: {
      type: String,
      default: null,
    },
    chartOptions: {
      type: Object,
      default: () => { return {} }
    },
  },
  computed: {
    isChartTypeSupported() {
      if (
        this.chartType === 'area' ||
        this.chartType === 'pie' ||
        this.chartType === 'table' ||
        this.chartType === 'geo' ||
        this.chartType === 'column'
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
  created() {
    window.addEventListener("resize", this.onResize);
  },
  destroyed() {
    window.removeEventListener("resize", this.onResize);
  },
  methods: {
    onResize() {
      this.drawChart();
    },
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
        case 'column': {
          this.drawColumnChart()
          break
        }
      }
    },

    drawAreaChart() {
      const chart = new google.visualization.AreaChart(this.$refs.chart)

      // Generate ticks
      const chartOptions = {
        ...this.chartOptions
      }
      chartOptions.hAxis.ticks = this.generateAreaTicks()

      // Format data table
      const chartData = this.formatAreaDataTable(this.chartData)

      chart.draw(chartData, chartOptions)
    },
    drawPieChart() {
      const chart = new google.visualization.PieChart(this.$refs.chart)
      chart.draw(this.chartData, this.chartOptions)
    },
    drawTableChart() {
      const chart = new google.visualization.Table(this.$refs.chart)
      chart.draw(this.chartData, this.chartOptions)
    },
    drawGeoChart() {
      const chart = new google.visualization.GeoChart(this.$refs.chart)
      chart.draw(this.chartData, this.chartOptions)
    },
    drawColumnChart() {
      const chart = new google.visualization.ColumnChart(this.$refs.chart)
      chart.draw(this.chartData, this.chartOptions)
    },
    generateAreaTicks() {
      const ticks = [];

      for (let i = 0; i < this.chartData.getNumberOfRows(); i++) {
        const tickDate = this.chartData.getValue(i, 0)
        ticks.push(tickDate)
      }

      return ticks
    },
    formatAreaDataTable(dataTable) {
      if (this.chartScale === 'year') {
        var dateFormatter = new google.visualization.DateFormat({
          pattern: "MMMM yyyy"
        });

        dateFormatter.format(dataTable, 0);
      }

      return dataTable
    }
  },
}
</script>