<template>
  <div>
    <!-- View -->
    <div class="field">
      <div class="heading">
        <label>View</label>
      </div>
      <div class="input">
        <div class="da-flex da-items-center da-gap-3">
          <div class="select">
            <select
              v-model="viewId"
              :name="inputName('viewId')"
              @change="onViewChange()"
            >
              <template v-for="(option, optionKey) in viewOptions">
                <option
                  :key="optionKey"
                  :value="option.value"
                >
                  {{ option.label }}
                </option>
              </template>
            </select>
          </div>
          <div
            class="spinner"
            :class="{
              'da-invisible': !loading,
            }"
          />
        </div>
      </div>
    </div>

    <!-- Chart Type -->
    <div class="field">
      <div class="heading">
        <label>Chart Type</label>
      </div>
      <div class="input">
        <div class="chart-picker">
          <div class="da-inline-block">
            <ul class="chart-types da-flex da-border da-border-gray-300/70 da-bg-white da-rounded-md">
              <template v-for="(option, optionKey) in chartTypeOptions">
                <li
                  :key="optionKey"
                  class="da-relative"
                >
                  <label>
                    <div>
                      <input
                        v-model="chart"
                        :name="inputName('chart')"
                        type="radio"
                        :value="option.value"
                        class="da-peer da-sr-only"
                        @change="onChartChange"
                      >
                      <div
                        class="da-text-gray-700 peer-checked:da-text-black da-px-4 da-py-3 da-cursor-pointer peer-focus-visible:da-ring-2 peer-focus-visible:da-z-50 peer-focus-visible:da-border-transparent da-relative"
                        :class="{
                          'da-bg-white hover:da-bg-gray-100/50': option.value !== chart,
                          'da-bg-gray-100': option.value === chart,
                          'da-rounded-l-md': optionKey === 0,
                          'da-rounded-r-md': optionKey === chartTypeOptions.length - 1,
                          'da-border-r': optionKey !== chartTypeOptions.length - 1,
                        }"
                      >
                        {{ option.label }}
                      </div>
                    </div>
                  </label>
                </li>
              </template>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <!-- Period -->
    <div class="field">
      <div class="heading">
        <label>Period</label>
      </div>
      <div class="input">
        <div class="select">
          <select
            v-model="period"
            :name="inputName('period')"
          >
            <template v-for="(option, optionKey) in periodOptions">
              <option
                :key="optionKey"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </template>
          </select>
        </div>
      </div>
    </div>

    <!-- Dimension -->
    <template v-if="chartSupportsDimensions">
      <div class="field">
        <div class="heading">
          <label>Dimension</label>
        </div>
        <div>
          <v-select
            v-model="dimension"
            :options="dimensionSelectOptions"
            :selectable="(option) => {
              if (option.optgroup) {
                return false;
              }

              return true
            }"
            :reduce="dimension => dimension.value"
          >
            <template #option="option">
              <template v-if="option.optgroup">
                ——— {{ option.label }} ———
              </template>
              <template v-else>
                {{ option.label }}
              </template>
            </template>
          </v-select>
          <input
            type="hidden"
            :name="inputName('options['+chart+'][dimension]')"
            :value="dimension"
          >
        </div>
      </div>
    </template>

    <!-- Metric -->
    <div class="field">
      <div class="heading">
        <label>Metric</label>
      </div>
      <div>
        <v-select
          v-model="metric"
          :options="metricSelectOptions"
          :selectable="(option) => {
            if (option.optgroup) {
              return false;
            }

            return true
          }"
          :reduce="metric => metric.value"
        >
          <template #option="option">
            <template v-if="option.optgroup">
              ——— {{ option.label }} ———
            </template>
            <template v-else>
              {{ option.label }}
            </template>
          </template>
        </v-select>
        <input
          type="hidden"
          :name="inputName('options['+chart+'][metric]')"
          :value="metric"
        >
      </div>
    </div>
  </div>
</template>

<script>
/* global Craft */

import VSelect from "vue-select";
import "vue-select/dist/vue-select.css";

import reportsApi from './api/reports';

export default {
  components: {
    VSelect,
  },
  data() {
    return {
      loading: false,
      pluginSettings: null,
      chartTypeOptions: [
        {
          label: 'Area',
          value: 'area',
        },
        {
          label: 'Counter',
          value: 'counter',
        },
        {
          label: 'Pie',
          value: 'pie',
        },
        {
          label: 'Table',
          value: 'table',
        },
        {
          label: 'Geo',
          value: 'geo',
        },
      ],
      periodOptions: [
        {
          label: 'Week',
          value: 'week'
        },
        {
          label: 'Month',
          value: 'month'
        },
        {
          label: 'Year',
          value: 'year'
        }
      ],
      vSelectedMetric: null,
      selectedDimension: null,

      viewId: null,
      chart: null,
      period: null,
      dimension: null,
      metric: null,
      dimensions: [],
      metrics: [],
    }
  },
  computed: {
    viewOptions() {
      return this.reportingViews.map(view => {
        return {
          label: view.name,
          value: view.id,
        }
      });
    },

    metricCategories() {
      return this.metrics
        .map(metric => metric.category)
        .filter((category, index, self) => self.indexOf(category) === index)
    },

    dimensionCategories() {
      return this._dimensions
        .map(dimension => dimension.category)
        .filter((category, index, self) => self.indexOf(category) === index)
    },

    metricOptions() {
      let options = [];

      this.metricCategories.forEach(category => {
        options.push({
          optgroup: category,
        })

        this.metrics.filter(metric => metric.category === category).forEach(metric => {
          options.push({
              label: metric.name,
              value: metric.apiName,
            }
          )
        })
      })

      return options;
    },

    _dimensions() {
      const geoDimensions = ['country', 'continent'];
      return this.dimensions
        // Filter geo dimensions
        .filter(dimension => (this.chart !== 'geo' || geoDimensions.find(geoDimension => geoDimension === dimension.apiName)))
        .map(dimension => {
          if (this.chart === 'geo') {
            switch(dimension.apiName) {
              case 'continent':
                return {
                  ...dimension,
                  apiName: 'continentId'
                }
            }
          }

          return dimension;
        });
    },

    dimensionOptions() {
      let options = [];

      this.dimensionCategories.forEach(category => {
        options.push({
          optgroup: category,
        })

        this._dimensions.filter(dimension => dimension.category === category).forEach(dimension => {
          options.push({
              label: dimension.name,
              value: dimension.apiName,
            }
          )
        })
      })

      return options;
    },

    chartSupportsDimensions() {
      return this.chart === 'pie' || this.chart === 'table' || this.chart === 'geo'
    },

    metricSelectOptions() {
      return this.parseOptionsForVueSelect(this.metricOptions)
    },

    dimensionSelectOptions() {
      return this.parseOptionsForVueSelect(this.dimensionOptions)
    },

    reportingViews() {
      return this.pluginSettings.reportingViews
    },
    selectOptions() {
      return this.pluginSettings.selectOptions
    },
    inputName() {
      return (name) => {
        if (!name) {
          return null
        }

        const namespace = this.pluginSettings.namespace

        return Craft.namespaceInputName(name, namespace)
      }
    }
  },
  mounted() {
    this.viewId = this.pluginSettings.settings.viewId ?? (this.viewOptions[0].value ?? null)
    this.chart = this.pluginSettings.settings.chart ?? this.chartTypeOptions[0].value
    this.period = this.pluginSettings.settings.period ?? this.periodOptions[0].value
    this.namespace = this.pluginSettings.namespace

    this.onViewChange()
  },

  methods: {
    onViewChange() {
      this.refreshDimensionsAndMetrics()
    },
    refreshDimensionsAndMetrics() {
      this.loading = true
      reportsApi.getDimensionsMetrics(this.viewId)
        .then((response) => {
          this.dimensions = response.data.dimensions
          this.metrics = response.data.metrics

          this.initMetric();
          this.initDimension();
        })
        .finally(() => {
          this.loading = false
        })
    },
    initMetric() {
      if (
        this.pluginSettings.settings.options
        && this.pluginSettings.settings.options[this.chart].metric
        && this.metricSelectOptions.find(option => option.value === this.pluginSettings.settings.options[this.chart].metric)
      ) {

        this.metric = this.pluginSettings.settings.options[this.chart].metric
      } else {
        this.metric = this.metricSelectOptions.find(option => option.value !== undefined)?.value
      }
    },
    initDimension() {
      if (
        this.pluginSettings.settings.options
        && this.pluginSettings.settings.options[this.chart]
        && this.pluginSettings.settings.options[this.chart].dimension

        // Check that the dimension exists in the options
        && this.dimensionSelectOptions.find(option => option.value === this.pluginSettings.settings.options[this.chart].dimension)
      ) {

        this.dimension = this.pluginSettings.settings.options[this.chart].dimension
      } else {
        this.dimension = this.dimensionSelectOptions.find(option => option.value !== undefined)?.value
      }
    },

    onChartChange() {
      // this.initMetric()
      // this.initDimension()
    },

    parseOptionsForVueSelect(inputOptions) {
      if (!inputOptions) {
        return []
      }

      const options = []

      for (let i = 0; i < inputOptions.length; i++) {
        const option = inputOptions[i]
        if (option.optgroup) {
          options.push({
            optgroup: true,
            label: option.optgroup,
          })
        } else {
          options.push({
            label: option.label,
            value: option.value,
          })
        }
      }

      return options
    },
  }
}
</script>