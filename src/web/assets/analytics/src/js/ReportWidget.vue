<template>
  <div v-if="requestCriteria && requestCriteria.chart">
    <template v-if="loading">
      <div>Loading…</div>
    </template>
    <template v-else>
      <analytics-report
        :report-response="reportResponse"
        :locale-definition="localeDefinition"
      />
    </template>
  </div>
</template>

<script>
import reportsApi from './api/reports'
import AnalyticsReport from '@/js/components/AnalyticsReport.vue';
import {getLocaleDefinition} from '@/js/utils';

export default {
  components: {
    AnalyticsReport
  },
  data() {
    return {
      loading: true,
      reportResponse: null,
      pluginOptions: null,
    }
  },
  computed: {
    requestCriteria() {
      return this.pluginOptions.request
    },
    localeDefinition() {
      return getLocaleDefinition(this.pluginOptions.currencyDefinition);
    }
  },
  watch: {
    requestCriteria() {
      this.getReport()
    }
  },
  mounted() {
    this.getReport()
  },
  methods: {
    getReport() {
      if (!this.requestCriteria) {
        return
      }

      this.loading = true

      reportsApi.getReport(this.requestCriteria)
        .then(response => {
          this.loading = false
          this.reportResponse = response
        });
    }
  }
}
</script>