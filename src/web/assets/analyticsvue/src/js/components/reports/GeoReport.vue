<template>
  <div class="da-border da-rounded-md da-p-6">
    <div>
      <select
        v-model="selectedDimension"
        @change="onDimensionChange"
      >
        <option
          v-for="(dimension, index) in dimensions"
          :key="index"
          :value="dimension.value"
        >
          {{ dimension.label }}
        </option>
      </select>

      {{ selectedDimension }}
    </div>

    <hr>
    <template v-if="loading">
      <div>Loading…</div>
    </template>
    <template v-else>
      <div>
        <div>
          <div
            class="da-font-bold"
          >
            {{ reportResponse.data.metric }}
          </div>
          <div>
            {{ reportResponse.data.periodLabel }}
          </div>
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
                  :chart-type="reportCriteria.chart"
                  :chart-data="chartData"
                />
              </template>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import reportsApi from '../../api/reports'
import AnalyticsChart from '@/js/components/AnalyticsChart';
import {responseToDataTable} from '@/js/utils'

export default {
  components: {
    AnalyticsChart
  },
  data() {
    return {
      loading: true,
      reportResponse: null,
      chartData: null,
      dimensions: [
        {
          label: 'ga:city',
          value: 'ga:city'
        },
        {
          label: 'ga:country',
          value: 'ga:country'
        },
        {
          label: 'ga:continent',
          value: 'ga:continent'
        },
        {
          label: 'ga:subContinent',
          value: 'ga:subContinent'
        },
      ],
      selectedDimension: 'ga:country',
    }
  },
  computed: {
    reportCriteria() {
      return {
        viewId: 1,
        chart: 'geo',
        period: 'month',
        options: {
          dimension: this.selectedDimension,
          metric: 'ga:users'
        },
      }
    }
  },
  mounted() {
    this.loading = true

    reportsApi.getReport(this.reportCriteria)
      .then(response => {
        this.loading = false
        this.reportResponse = response
        this.chartData = responseToDataTable(response.data.chart)
      });
  },
  methods: {
    onDimensionChange() {
      this.loading = true

      reportsApi.getReport(this.reportCriteria)
        .then(response => {
          this.loading = false
          this.reportResponse = response
          this.chartData = responseToDataTable(response.data.chart)
        });
    }
  }
}
</script>