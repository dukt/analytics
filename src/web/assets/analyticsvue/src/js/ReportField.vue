<template>
  <div class="da-mt-8">
    <div class="da-flex da-items-center">
      <div>
        <div class="field">
          <div class="select">
            <select
              v-model="metric"
              @change="getElementReport()"
            >
              <template v-for="(metricOption, metricOptionKey) in metricOptions">
                <option
                  :key="metricOptionKey"
                  :value="metricOption.value"
                >
                  {{ metricOption.label }}
                </option>
              </template>
            </select>
          </div>
        </div>
      </div>
      <div class="da-flex-1 da-ml-4 da-text-right">
        {{ pluginOptions.element.url }}
      </div>
    </div>

    <template v-if="loading">
      Loading…
    </template>
    <template v-else>
      <template v-if="reportResponse">
        <analytics-chart
          :chart-type="reportResponse.data.type"
          :chart-data="chartData"
          :chart-options="chartOptions"
          class="da-mt-4"
        />
      </template>
    </template>

    <ul class="da-mt-4">
      <li>elementId: {{ elementId }}</li>
      <li>siteId: {{ siteId }}</li>
      <li>metric: {{ metric }}</li>
    </ul>
  </div>
</template>

<script>
/* global google */

import reportsApi from '@/js/api/reports';
import {ChartOptions} from '@/js/ChartOptions';
import AnalyticsChart from '@/js/components/AnalyticsChart.vue';
import {getLocaleDefinition, responseToDataTable} from '@/js/utils';

export default {
  components: {AnalyticsChart},
  data() {
    return {
      loading: false,
      pluginOptions: null,
      reportType: null,
      chartData: null,
      reportResponse: null,
      metricOptions: [
        {
          label: 'Screen Page Views',
          value: 'screenPageViews',
        },
        {
          label: 'Sessions',
          value: 'sessions',
        },
        {
          label: 'Bounce Rate',
          value: 'bounceRate',
        },
        {
          label: 'Avg. time on page',
          value: 'avgTimeOnPage',
        },
        {
          label: '% Exit',
          value: 'exitRate',
        },
      ],
      metric: 'screenPageViews',
    }
  },

  computed: {
    elementId() {
      return this.pluginOptions.element.id
    },
    siteId() {
      return this.pluginOptions.element.siteId
    },
    chartOptions() {
      return new ChartOptions().field()
    },
    localeDefinition() {
      return getLocaleDefinition(this.pluginOptions.currencyDefinition);
    }
    // chartOptions() {
    //   const chartOptions = new ChartOptions().area(this.reportResponse.data.period)
    //   chartOptions.hAxis.ticks = this.generateTicks()
    //   return chartOptions
    // },
  },

  mounted() {
    this.getElementReport()
  },

  methods: {
    getElementReport() {
      this.loading = true

      reportsApi.getElementReport({
          elementId: this.elementId,
          siteId: this.siteId,
          metric: this.metric,
        })
        .then(response => {
          this.loading = false
          this.reportResponse = response
          this.reportType = response.data.type

          const dataTable = responseToDataTable(this.reportResponse.data.chart, this.localeDefinition)
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