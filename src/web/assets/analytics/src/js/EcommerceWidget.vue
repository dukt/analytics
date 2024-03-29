<template>
  <div>
    <template v-if="loading">
      {{ t('analytics', "Loading…") }}
    </template>
    <template v-if="reportResponse">
      <div>
        {{ reportResponse.data.reportData.periodLabel }}
      </div>
      <div class="da-text-gray-500">
        {{ siteName }}
      </div>
      <template v-if="chartData">
        <analytics-chart
          chart-type="area"
          :chart-data="chartData"
          :chart-options="chartOptions"
          :chart-scale="reportResponse.data.reportData.period"
          class="da-mt-4"
        />
      </template>
      <div class="tiles">
        <div class="tile">
          <div class="label light">
            {{ t('analytics', "Total Revenue") }}
          </div>
          <div class="value revenue">
            {{ totalRevenue }}
          </div>
        </div>
        <div class="tile">
          <div class="label light">
            {{ t('analytics', "Revenue Per Transaction") }}
          </div>
          <div class="value revenue-per-transaction">
            {{ totalRevenuePerTransaction }}
          </div>
        </div>
        <div class="tile">
          <div class="label light">
            {{ t('analytics', "Transactions") }}
          </div>
          <div class="value transactions">
            {{ totalTransactions }}
          </div>
        </div>
        <div class="tile">
          <div class="label light">
            {{ t('analytics', "Transactions Per Session") }}
          </div>
          <div class="value transactions-per-session">
            {{ totalTransactionsPerSession }}
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import reportsApi from '@/js/api/reports';
import {responseToDataTable, getLocaleDefinition, formatByType, t} from '@/js/utils';
import {ChartOptions} from '@/js/ChartOptions';
import AnalyticsChart from '@/js/components/AnalyticsChart.vue';

export default {
  components: {AnalyticsChart},
  data() {
    return {
      loading: false,
      reportResponse: null,
      pluginOptions: null,

      totalRevenue: 0,
      totalRevenuePerTransaction: 0,
      totalTransactions: 0,
      totalTransactionsPerSession: 0,

      chartData: null,
    }
  },
  computed: {
    siteName() {
      if (!this.reportResponse) {
        return null;
      }

      return this.reportResponse.data.reportData.view
    },
    chartOptions() {
      return new ChartOptions().area(this.pluginOptions.period)
    }
  },
  mounted() {
    this.getReport()
  },
  methods: {
    t,
    getReport() {
      this.loading = true

      reportsApi.getEcommerceReport({
          sourceId: this.pluginOptions.sourceId,
          period: this.pluginOptions.period,
        })
        .then(response => {
          this.loading = false
          this.reportResponse = response

          const localeDefinition = getLocaleDefinition(this.pluginOptions.currencyDefinition)

          this.totalRevenue = formatByType(localeDefinition, 'currency', response.data.totalRevenue)
          this.totalRevenuePerTransaction = formatByType(localeDefinition, 'currency', response.data.totalRevenuePerTransaction)
          this.totalTransactions = formatByType(localeDefinition, 'number', response.data.totalTransactions)
          this.totalTransactionsPerSession = formatByType(localeDefinition, 'number', response.data.totalTransactionsPerSession)

          const chartDataRows = [];

          response.data.reportData.chart.rows.forEach(row => {
            chartDataRows.push([
              row[0],
              row[1],
            ])
          })

          const chartData = {
            cols: [
              response.data.reportData.chart.cols[0],
              response.data.reportData.chart.cols[1],
            ],
            rows: chartDataRows,
          }

          this.chartData = responseToDataTable(chartData, localeDefinition)
        });
    }
  }
}
</script>

<style lang="pcss">
.tiles {
  margin-top: 10px;
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;

  .tile {
    margin-top: 14px;
    flex-grow: 1;
    flex-basis: 50%;
    flex-shrink: 0;
    box-sizing: border-box;
    overflow: hidden;

    .value {
      font-size: 16px;
    }
  }
}
</style>