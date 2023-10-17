<template>
  <div>
    <h2>JS Formatting</h2>

    <table
      id="formatByType"
      class="data fullwidth formatting-tests"
    >
      <thead>
        <tr>
          <th>Type</th>
          <th>Value</th>
          <th>Ouput</th>
        </tr>
      </thead>

      <tbody>
        <template v-for="(row, rowKey) in jsData">
          <tr :key="rowKey">
            <td>{{ row.type }}</td>
            <td>{{ row.value }}</td>
            <td>{{ formatByType(row.type, row.value) }}</td>
          </tr>
        </template>
      </tbody>
    </table>

    <h2>D3 Number Formatting</h2>

    <table
      id="d3NumberFormatting"
      class="data fullwidth formatting-tests"
    >
      <thead>
        <tr>
          <th>Label</th>
          <th>Format</th>
          <th>Value</th>
          <th>Formatted Value</th>
        </tr>
      </thead>
      <tbody>
        <template v-for="(row, rowKey) in d3Data">
          <tr :key="rowKey">
            <td>{{ row.label }}</td>
            <td>{{ row.format }}</td>
            <td>{{ row.value }}</td>
            <td>{{ d3FormatByFormat(row.format, row, row.value) }}</td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</template>

<script>
import {formatByType, getLocaleDefinition} from "@/js/utils";

export default {
  computed: {
    formats() {
      return Craft.charts.BaseChart.defaults.formats;
    },

    localeDefinition() {
      return getLocaleDefinition(window.AnalyticsCurrencyDefinition)
    },

    jsData() {
      return [
        { type: 'string', value: "Hello world!" },
        { type: 'percent', value: 36.7869 },
        { type: 'integer', value: 367869 },
        { type: 'time', value: 3200 },
        { type: 'time', value: 36786 },
        { type: 'currency', value: 36786 },
        { type: 'float', value: 36.7869 },
        { type: 'date', value: '201601' },
        { type: 'date', value: '20160203' },
      ]
    },

    d3Data() {
      return [
        {
          label: "percentFormat",
          value: 19.345,
          valueModifier: function(value) {
            return value / 100;
          },
          format: this.formats.percentFormat,
        },
        { label: "currencyFormat", value: 19.34, format: this.formats.currencyFormat },
        { label: "currencyFormat", value: 1902345.34, format: this.formats.currencyFormat },
        { label: "decimalFormat", value: 1902345.3412344, format: this.formats.numberFormat }
      ]
    }
  },

  methods: {
    formatByType(type, value) {
      return formatByType(this.localeDefinition, type, value)
    },

    d3FormatByFormat(format, row, value) {
      var locale = d3.formatLocale(this.localeDefinition);

      if(typeof(row.valueModifier) != 'undefined') {
        value = row.valueModifier(value);
      }

      return locale.format(format)(value);
    }
  }
}
</script>