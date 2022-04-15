<template>
  <div>
    <div class="field">
      <div class="heading">
        <label>View</label>
      </div>
      <div class="input">
        <div class="select">
          <select v-model="viewId">
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
                        v-model="chart"
                        type="radio"
                        :value="option.value"
                        class="da-peer da-sr-only"
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


    <div class="field">
      <div class="heading">
        <label>Period</label>
      </div>
      <div class="input">
        <div class="select">
          <select v-model="period">
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
          <select v-model="metric">
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
      {{ metric }}
      <!-- array of strings or numbers -->
      <v-select
        v-model="metric"
        :options="vSelectMetricOptions"
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
    </div>

    <div>
      <ul>
        <li>{{ selectedDimension }}</li>
        <li>{{ selectedMetric }}</li>
      </ul>
    </div>
  </div>
</template>

<script>
import VSelect from 'vue-select';

export default {
  components: {
    VSelect,
  },
  data() {
    return {
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
      metric: null,
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
      const options = this.selectOptions[this.chart].metrics

      console.log('options', options)

      return options
    },

    dimensionOptions() {
      if (!this.selectOptions) {
        return []
      }
      const options = this.selectOptions[this.chart].dimensions

      console.log('options', options)

      return options
    },
    hasDimensions() {
      return this.chart === 'pie' || this.chart === 'table' || this.chart === 'geo'
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
    },
    reportingViews() {
      return this.pluginSettings.reportingViews
    },
    selectOptions() {
      return this.pluginSettings.selectOptions
    },
  },
  mounted() {
    this.viewId = this.pluginSettings.settings.viewId
    this.chart = this.pluginSettings.settings.chart
    this.period = this.pluginSettings.settings.period
    this.metric = this.pluginSettings.settings.options[this.chart].metric
  },
}
</script>