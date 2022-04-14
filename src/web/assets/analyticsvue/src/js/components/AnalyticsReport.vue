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
      <template v-else-if="reportType === 'pie'">
        <pie-report :report-response="reportResponse" />
      </template>
      <template v-else-if="reportType === 'table'">
        <table-report :report-response="reportResponse" />
      </template>
      <template v-else-if="reportType === 'geo'">
        <geo-report :report-response="reportResponse" />
      </template>
    </div>
  </div>
</template>

<script>
import AreaReport from '@/js/components/reports/AreaReport';
import CounterReport from '@/js/components/reports/CounterReport';
import PieReport from '@/js/components/reports/PieReport';
import TableReport from '@/js/components/reports/TableReport';
import GeoReport from '@/js/components/reports/GeoReport';

export default {
  components: {GeoReport, TableReport, PieReport, CounterReport, AreaReport},
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