<template>
  <div class="da-bg-gray-100 da-border da-rounded-md da-p-6">
    <div class="field">
      <div class="heading">
        <label>View</label>
      </div>
      <div class="input">
        <div class="select">
          <select v-model="selectedReportingView">
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
      </div>
    </div>

    <div class="field">
      <div class="heading">
        <label>Chart Type</label>
      </div>
      <div class="input">
        <div>
          <div class="da-inline-block">
            <ul class="da-flex da-border da-border-gray-300/70 da-bg-white da-rounded-md">
              <template v-for="(option, optionKey) in chartTypeOptions">
                <li
                  :key="optionKey"
                  class="da-relative"
                >
                  <label>
                    <div>
                      <input
                        v-model="selectedChart"
                        type="radio"
                        :value="option.value"
                        class="da-peer da-sr-only"
                      >
                      <div
                        class="da-text-gray-700 peer-checked:da-text-black da-px-4 da-py-3 da-cursor-pointer peer-focus-visible:da-ring-2 peer-focus-visible:da-z-50 peer-focus-visible:da-border-transparent da-relative"
                        :class="{
                          'da-bg-white hover:da-bg-gray-100/50': option.value !== selectedChart,
                          'da-bg-gray-100': option.value === selectedChart,
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


    <div class="field">
      <div class="heading">
        <label>Period</label>
      </div>
      <div class="input">
        <div class="select">
          <select v-model="selectedPeriod">
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

    <template v-if="hasDimensions">
      <div class="field">
        <div class="heading">
          <label>Dimension</label>
        </div>
        <div class="input">
          <div class="select">
            <select v-model="selectedDimension">
              <template v-for="(option, optionKey) in dimensionOptions">
                <template v-if="option.optgroup">
                  <optgroup
                    :key="optionKey"
                    :label="option.optgroup"
                  />
                </template>
                <template v-else>
                  <option
                    :key="optionKey"
                    :value="option.value"
                  >
                    {{ option.label }}
                  </option>
                </template>
              </template>
            </select>
          </div>
        </div>
      </div>
    </template>

    <div class="field">
      <div class="heading">
        <label>Metric</label>
      </div>
      <div class="input">
        <div class="select">
          <select v-model="vSelectedMetric">
            <template v-for="(option, optionKey) in metricOptions">
              <template v-if="option.optgroup">
                <optgroup
                  :key="optionKey"
                  :label="option.optgroup"
                />
              </template>
              <template v-else>
                <option
                  :key="optionKey"
                  :value="option.value"
                >
                  {{ option.label }}
                </option>
              </template>
            </template>
          </select>
        </div>
      </div>
    </div>

    <div>
      <!-- array of strings or numbers -->
      <v-select
        v-model="vSelectedMetric"
        :options="vSelectMetricOptions"
        :selectable="(option) => {
          if (option.optgroup) {
            return false;
          }

          return true
        }"
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
    </div>

    <div>
      <ul>
        <li>{{ selectedReportingView }}</li>
        <li>{{ selectedChart }}</li>
        <li>{{ selectedPeriod }}</li>
        <li>{{ selectedDimension }}</li>
        <li>{{ selectedMetric }}</li>
      </ul>
    </div>

    <div class="da-mt-6">
      <input
        type="button"
        value="Update Chart"
        class="da-bg-red-600 da-text-white da-rounded-md da-px-4 da-py-2 da-cursor-pointer"
        @click="updateCriteria"
      >
    </div>
  </div>
</template>

<script>
import reportsApi from '@/js/api/reports';

// import Vue from "vue";
import VSelect from "vue-select";

// Vue.component("v-select", vSelect);
import "vue-select/dist/vue-select.css";

export default {
  components: {
    VSelect,
  },
  data() {
    return {
      reportingViews: [],
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
      selectedReportingView: null,
      selectedChart: 'area',
      selectedPeriod: 'week',
      vSelectedMetric: null,
      selectedDimension: null,
      selectOptions: null,
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

    metricOptions() {
      if (!this.selectOptions) {
        return []
      }
      const options = this.selectOptions[this.selectedChart].metrics

      console.log('options', options)

      return options
    },

    dimensionOptions() {
      if (!this.selectOptions) {
        return []
      }
      const options = this.selectOptions[this.selectedChart].dimensions

      console.log('options', options)

      return options
    },
    hasDimensions() {
      return this.selectedChart === 'pie' || this.selectedChart === 'table' || this.selectedChart === 'geo'
    },

    vSelectMetricOptions() {
      const inputOptions = this.metricOptions
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
    selectedMetric() {
      if (!this.vSelectedMetric) {
        return null
      }
      
      return this.vSelectedMetric.value
    }
  },
  mounted() {
    this.loading = true

    reportsApi.getReportWidgetSettings()
      .then(response => {
        this.loading = false
        this.reportingViews = response.data.views
        this.selectOptions = response.data.selectOptions
        this.selectedReportingView = this.reportingViews[0].id
      });
  },
  methods: {
    updateCriteria() {
      const options = {
        metric: this.selectedMetric
      }

      if (this.hasDimensions) {
        options.dimension = this.selectedDimension
      }

      this.$emit('update-criteria', {
        viewId: this.selectedReportingView,
        chart: this.selectedChart,
        period: this.selectedPeriod,
        options,
      });
    }
  }
}
</script>