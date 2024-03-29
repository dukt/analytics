<template>
  <div>
    <!-- Source -->
    <div class="field">
      <div class="heading">
        <label>{{ t('analytics', "Source") }}</label>
      </div>
      <div class="input">
        <div class="da-flex da-items-center da-gap-3">
          <div class="select">
            <select
              v-model="sourceId"
              :name="inputName('sourceId')"
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
        <label>{{ t('analytics', "Chart Type") }}</label>
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
                        :title="option.label"
                      >
                        <template v-if="option.value === 'area'">
                          <ChartArea class="da-w-4 da-h-4" />
                        </template>
                        <template v-else-if="option.value === 'counter'">
                          <NumberIcon class="da-w-4 da-h-4" />
                        </template>
                        <template v-else-if="option.value === 'pie'">
                          <ChartPieIcon class="da-w-4 da-h-4" />
                        </template>
                        <template v-else-if="option.value === 'table'">
                          <TableIcon class="da-w-4 da-h-4" />
                        </template>
                        <template v-else-if="option.value === 'geo'">
                          <EarthIcon class="da-w-4 da-h-4" />
                        </template>
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
        <label>{{ t('analytics', "Period") }}</label>
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
          <label>{{ t('analytics', "Dimension") }}</label>
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
            :name="inputName('options[dimension]')"
            :value="dimension"
          >
        </div>
      </div>
    </template>

    <!-- Metric -->
    <div class="field">
      <div class="heading">
        <label>{{ t('analytics', "Metric") }}</label>
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
          :name="inputName('options[metric]')"
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
import ChartPieIcon from "@/js/components/icons/ChartPieIcon.vue";
import EarthIcon from "@/js/components/icons/EarthIcon.vue";
import TableIcon from "@/js/components/icons/TableIcon.vue";
import NumberIcon from "@/js/components/icons/NumberIcon.vue";
import ChartArea from "@/js/components/icons/ChartArea.vue";
import {t} from '@/js/utils';

export default {
  components: {
    ChartArea,
    NumberIcon,
    TableIcon,
    EarthIcon,
    ChartPieIcon,
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

      sourceId: null,
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
      return this.sources.map(view => {
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

    sources() {
      return this.pluginSettings.sources
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
    this.sourceId = this.pluginSettings.settings.sourceId ?? (this.viewOptions[0].value ?? null)
    this.chart = this.pluginSettings.settings.chart ?? this.chartTypeOptions[0].value
    this.period = this.pluginSettings.settings.period ?? this.periodOptions[0].value
    this.namespace = this.pluginSettings.namespace

    this.onViewChange()
  },

  methods: {
    t,
    onViewChange() {
      this.refreshDimensionsAndMetrics()
    },
    refreshDimensionsAndMetrics() {
      this.loading = true
      reportsApi.getDimensionsMetrics(this.sourceId)
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
      // if (this.metricSelectOptions.find(option => option.value === this.metric)) {
      //   // The metric is already selected
      //   return;
      // }

      const options = this.pluginSettings.settings.options;

      if (
        options?.[this.chart]?.metric &&
        this.metricSelectOptions.find(option => option.value === options[this.chart].metric)
      ) {
        // Select metric from the saved chart options with the same type
        this.metric = options[this.chart].metric;
      } else if (
        Object.values(options).length > 0 &&
        Object.values(options)[0].metric &&
        this.metricSelectOptions.find(option => option.value === Object.values(options)[0].metric)
      ) {
        // Select metric from the saved chart options with a different type
        this.metric = Object.values(options)[0].metric;
      } else {
        // Select first metric in the list
        this.metric = this.metricSelectOptions.find(option => option.value !== undefined)?.value;
      }
    },

    initDimension() {
      // if (this.dimensionSelectOptions.find(option => option.value === this.dimension)) {
      //   // The dimension is already selected
      //   return;
      // }

      const options = this.pluginSettings.settings.options;

      if (
        options?.[this.chart]?.dimension &&
        this.dimensionSelectOptions.find(option => option.value === options[this.chart].dimension)
      ) {
        // Select dimension from the saved chart options with the same type
        this.dimension = options[this.chart].dimension;
      } else if (
        Object.values(options).length > 0 &&
        Object.values(options)[0].dimension &&
        this.dimensionSelectOptions.find(option => option.value === Object.values(options)[0].dimension)
      ) {
        // Select dimension from the saved chart options with a different type
        this.dimension = Object.values(options)[0].dimension;
      } else {
        // Select first dimension in the list
        this.dimension = this.dimensionSelectOptions.find(option => option.value !== undefined)?.value;
      }
    },

    onChartChange() {
      this.initMetric()
      this.initDimension()
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