<template>
  <div class="da-border da-rounded-md da-p-6">
    <div v-if="requestCriteria && requestCriteria.chart">
      <div>
        <div
          class="da-font-bold"
        >
          {{ requestCriteria.options.metric }}
        </div>
        <div>{{ requestCriteria.period }}</div>
        <div
          class="da-text-gray-500"
        >
          Site Name
        </div>
        <div
          class="da-mt-4"
        >
          <template v-if="loading">
            <div>Loading…</div>
          </template>
          <template v-else>
            <div>
              <analytics-report
                :report-response="reportResponse"
              />

              <template v-if="debug">
                <div class="da-relative da-mt-6">
                  <pre class="da-border da-rounded-md da-bg-gray-100 da-w-96 da-h-96 da-overflow-auto">
                    <code class="da-min-w-full da-p-4">{{ reportResponse }}</code>
                  </pre>
                </div>
              </template>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import reportsApi from './api/reports'
import AnalyticsReport from '@/js/components/AnalyticsReport';

export default {
  components: {
    AnalyticsReport
  },
  props: {
    requestCriteria: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      debug: false,
      loading: true,
      reportResponse: null,
    }
  },
  watch: {
    requestCriteria() {
      console.log('updaterequestCriteria')
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