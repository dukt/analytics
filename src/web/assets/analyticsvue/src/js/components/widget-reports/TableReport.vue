<template>
  <div>
    <report-header :report-response="reportResponse" />

    <div
      class="da-mt-4"
    >
      <div>
        <template v-if="chartData">
          <analytics-chart
            class="table-report"
            :chart-type="reportResponse.data.type"
            :chart-data="chartData"
            :chart-options="chartOptions"
          />
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import AnalyticsChart from '@/js/components/AnalyticsChart';
import {responseToDataTable} from '@/js/utils'
import {ChartOptions} from '@/js/ChartOptions';
import ReportHeader from '@/js/components/ReportHeader';

export default {
  components: {
    ReportHeader,
    AnalyticsChart
  },
  props: {
    reportResponse: {
      type: Object,
      required: true
    },
    localeDefinition: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      chartData: null,
    }
  },
  computed: {
    chartOptions() {
      return new ChartOptions().table()
    }
  },
  mounted() {
    this.chartData = responseToDataTable(this.reportResponse.data.chart, this.localeDefinition)
  }
}
</script>

<style>
.table-report {
  overflow: visible;
  z-index: 1;
  margin: 0 0 0 0;
  position: relative;
  min-height: 30px;
  border-top: 1px dotted #e3e5e8;
  min-height: 60px;

  & > div {
    position: inherit;
  }

  td strong {
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
    width: 200px;
    display: block;
    overflow: hidden;
    font-weight: normal;
  }

  .google-visualization-table {
    width: 100%;
    border: 0;
  }
  .google-visualization-table-table {
    width: 100%;
  }

  thead {
    tr {
      background: none;

      .google-visualization-table-th {
        background: none !important;
        padding-left: 0;
        padding-right: 0;
        border: 0;
        border-bottom: 1px dotted #e3e5e8;
        color: rgba(0, 0, 0, 0.298039);
        font-size: 0.8em;
        white-space: nowrap;

        &:first-child {
          text-align: left;
        }

        &:last-child {
          text-align: right;
        }

        span {
          display: none;
        }
      }
    }
  }

  td.google-visualization-table-td {
    max-width: 100px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    background: #fff;
    border-left: 0;
    border-right: 0;
    border-top: 0;
    border-bottom: 1px dotted #e3e5e8;

    &:hover {
      background: #fff;
    }

    &.google-visualization-table-td-number {
      text-align: right;
    }
  }

  .google-visualization-table-tr-sel td,
  .google-visualization-table-tr-over td {
    background-color: #fff;
  }

  .google-visualization-table-tr-sel.google-visualization-table-tr-odd,
  .google-visualization-table-tr-over.google-visualization-table-tr-odd {
    background-color: #fff;
  }

  .google-visualization-table-div-page {
    text-align: center;
    position: relative;
    width: 100%;
    margin-top: 7px;
    background: none;

    .google-visualization-table-page-numbers {
      display: none;
    }

    .goog-custom-button {
      background: #fff;
      border: 1px solid #ddd;
      padding: 4px 14px;
      border-radius: 4px;

      &[role="button"].goog-custom-button-disabled {
        cursor: default;
      }

      &:not(:last-child) {
        margin-right: 7px;
      }

      .goog-custom-button-outer-box {
        border: 0;

        .goog-custom-button-inner-box {
          border: 0;

          span {
            font-size: 1.8em;
            line-height: 1em;
          }
        }
      }
    }
  }
}
</style>