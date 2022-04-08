<template>
  <div class="da-bg-gray-100 da-border da-rounded-md da-p-6">
    <div class="field">
      <div class="heading">
        <label>View</label>
      </div>
      <div class="input">
        <div class="select">
          <select>
            <template v-for="(option, optionKey) in viewOptions">
              <option :key="optionKey">
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
          <select>
            <template v-for="(option, optionKey) in periodOptions">
              <option :key="optionKey">
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
          <select>
            <template v-for="(option, optionKey) in []">
              <option :key="optionKey">
                {{ option.label }}
              </option>
            </template>
          </select>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
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
      viewOptions: [
        {
          label: 'Dukt.net',
          value: 'dukt.Net'
        }
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
      ]
    }
  }
}
</script>