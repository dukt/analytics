<template>
  <div>
    <div class="da-text-gray-500">
      {{ pluginOptions.reportingView.name }}
    </div>
    <div class="da-mt-2 da-text-6xl">
      {{ activeUsers }}
    </div>

    <div class="da-mt-6">
      <h3>
        Active users per minute
      </h3>

      <div>
        <template v-if="chartData">
          <analytics-chart
            chart-type="column"
            :chart-data="chartData"
            :chart-options="chartOptions"
          />
        </template>
      </div>
    </div>

    <div class="active-pages da-mt-6">
      <h3>
        Active pages
      </h3>

      <template v-if="activePages">
        <table class="data fullwidth">
          <thead>
            <tr>
              <th class="col-page">
                Page
              </th>
              <th class="col-users">
                Users
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(activePageRow, activePageKey) in activePages.rows"
              :key="activePageKey"
            >
              <td class="col-page">
                {{ activePageRow[0] }}
              </td>
              <td class="col-users">
                {{ activePageRow[1] }}
              </td>
            </tr>
          </tbody>
        </table>
      </template>
    </div>
  </div>
</template>

<script>
/* global google, Craft */
import reportsApi from '@/js/api/reports';
import AnalyticsChart from '@/js/components/AnalyticsChart.vue';

export default {
  components: {AnalyticsChart},
  data() {
    return {
      refreshInterval: 60,
      pluginOptions: null,
      reportResponse: null,

      activeUsers: 0,
      activePages: null,
      pageviews: null,

      chartData: null,
    }
  },

  computed: {
    chartOptions() {
      return {
        height: 150,
        //backgroundColor: '#eee',
        theme: 'maximized',
        bar: {groupWidth: "90%"},
        legend: {
          position: 'bottom',
        },
        hAxis: {
          direction: -1,
          baselineColor: 'transparent',
          gridlineColor: 'transparent',
          textPosition: 'none',
          gridlines: {
            count: 0
          },
        },
        vAxis: {
          baselineColor: '#fff',
          gridlineColor: '#fff',
          textPosition: 'none',
          gridlines: {
            count: 0
          },
        }
      }
    }
  },

  mounted() {
    this.refreshInterval = this.pluginOptions.refreshInterval;

    this.getReport();
  },

  methods: {
    getReport() {
      this.loading = true

      reportsApi.getRealtimeReport({
          viewId: this.pluginOptions.reportingView.id,
        })
        .then(response => {
          this.loading = false

          // Parse response data
          this.reportResponse = response
          this.activeUsers = this.reportResponse.data.activeUsers
          this.activePages = this.reportResponse.data.activePages
          this.parsePageviews(this.reportResponse)

          // Ready to go again
          setTimeout(this.getReport, this.refreshInterval * 1000)
        });
    },

    parsePageviews(reportResponse) {
      console.log('reportResponse', reportResponse.data.pageviews)
      this.pageviews = reportResponse.data.pageviews

      this.chartData = this.getPageviewsDataTable(this.pageviews);

      // this.chart = new google.visualization.ColumnChart(this.$pageviewsChart.get(0));
      // this.chart.draw(this.chartData, this.chartOptions);
    },

    getPageviewsDataTable(pageviews) {
      let data = new google.visualization.DataTable();
      data.addColumn('number', Craft.t('analytics', 'Minutes ago'));
      data.addColumn('number', Craft.t('analytics', 'Active Users'));

      if (pageviews.rows) {
        for (let minutesAgo = 30; minutesAgo >= 0; minutesAgo--) {
          let rowPageviews = 0;

          pageviews.rows.forEach((row) => {
            var rowMinutesAgo = parseInt(row[0]);

            if (rowMinutesAgo === minutesAgo) {
              rowPageviews = parseInt(row[1]);
            }
          });

          let minutesAgoFormatted = Craft.t('analytics', '{count} minutes ago', {count: minutesAgo})

          if (minutesAgo === 1) {
            minutesAgoFormatted = Craft.t('analytics', '{count} minute ago', {count: minutesAgo})
          }

          data.addRow([{v: minutesAgo, f: minutesAgoFormatted}, rowPageviews]);
        }
      }

      return data
    },

    // if (pageviews.rows && pageviews.rows.length > 0) {
    //   this.$pageviewsChart.removeClass('hidden');
    //   this.$pageviewsNoData.addClass('hidden');
    // } else {
    //   this.$pageviewsChart.addClass('hidden');
    //   this.$pageviewsNoData.removeClass('hidden');
    // }
  }
}
</script>