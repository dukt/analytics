<template>
  <div>
    <div>
      <div>
        reportType: {{ reportType }}
      </div>
    </div>
    <div>
      <template v-if="reportType === 'area'">
        <area-report :report-response="reportResponse" />
      </template>
      <template v-else-if="reportType === 'counter'">
        <counter-report :report-response="reportResponse" />
      </template>
    </div>
  </div>
</template>

<script>
import AreaReport from '@/js/components/reports/AreaReport';
import CounterReport from '@/js/components/reports/CounterReport';

export default {
  components: {CounterReport, AreaReport},
  props: {
    reportResponse: {
      type: Object,
      required: true,
    }
  },
  computed: {
    reportType() {
      if (!this.reportResponse) {
        return null
      }

      return this.reportResponse.data.type;
    }
  }
}
</script>