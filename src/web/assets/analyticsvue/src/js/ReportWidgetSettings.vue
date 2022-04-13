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
            <ul class="da-flex da-border da-bg-white da-rounded-md">
              <template v-for="(option, optionKey) in chartTypeOptions">
                <li :key="optionKey">
                  <div
                    class="da-px-4 da-py-3 hover:da-bg-gray-100/50 da-cursor-pointer"
                    :class="{
                      'da-border-r': optionKey !== chartTypeOptions.length - 1,
                    }"
                  >
                    <input
                      v-model="selectedChart"
                      type="radio"
                      :value="option.value"
                    >
                    {{ option.label }}
                  </div>
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

    <div class="field">
      <div class="heading">
        <label>Metric</label>
      </div>
      <div class="input">
        <div class="select">
          <select v-model="selectedMetric">
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
      <ul>
        <li>{{ selectedReportingView }}</li>
        <li>{{ selectedChart }}</li>
        <li>{{ selectedPeriod }}</li>
        <li>{{ selectedMetric }}</li>
      </ul>
    </div>
  </div>
</template>

<script>
import reportsApi from '@/js/api/reports';

export default {
  data() {
    return {
      reportingViews: [],
      chartTypeOptions: [
        {
          label: 'Area',
          value: 'area',
        },
        {
          label: 'Count',
          value: 'count',
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
      selectedMetric: null,
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
  }
}
</script>