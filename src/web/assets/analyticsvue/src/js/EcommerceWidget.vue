<template>
  <div>
    <template v-if="loading">
      Loading…
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
          class="da-mt-4"
        />
      </template>
      <div class="tiles">
        <div class="tile">
          <div class="label light">
            {{ "Total Revenue" }}
          </div>
          <div class="value revenue">
            {{ totalRevenue }}
          </div>
        </div>
        <div class="tile">
          <div class="label light">
            {{ "Average Order" }}
          </div>
          <div class="value revenue-per-transaction">
            {{ totalRevenuePerTransaction }}
          </div>
        </div>
        <div class="tile">
          <div class="label light">
            {{ "Transactions" }}
          </div>
          <div class="value transactions">
            {{ totalTransactions }}
          </div>
        </div>
        <div class="tile">
          <div class="label light">
            {{ "Conversion Rate" }}
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
import {responseToDataTable, getLocaleDefinition, formatByType} from '@/js/utils';
import {ChartOptions} from '@/js/ChartOptions';
import AnalyticsChart from '@/js/components/AnalyticsChart';

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
      return new ChartOptions().area()
    }
  },
  mounted() {
    this.getReport()
  },
  methods: {
    getReport() {
      this.loading = true

      reportsApi.getEcommerceReport({
          viewId: this.pluginOptions.viewId,
          period: this.pluginOptions.period,
        })
        .then(response => {
          this.loading = false
          this.reportResponse = response

          const localeDefinition = getLocaleDefinition(this.pluginOptions.currencyDefinition)

          this.totalRevenue = formatByType(localeDefinition, 'currency', response.data.totalRevenue)
          this.totalRevenuePerTransaction = formatByType(localeDefinition, 'currency', response.data.totalRevenuePerTransaction)
          this.totalTransactions = formatByType(localeDefinition, 'number', response.data.totalTransactions)
          this.totalTransactionsPerSession = formatByType(localeDefinition, 'percent', response.data.totalTransactionsPerSession)

          this.chartData = responseToDataTable(response.data.reportData.chart, localeDefinition)
        });
    }
  }
}
</script>