<template>
  <div>
    <template v-if="reportType === 'area'">
      <area-report
        :report-response="reportResponse"
        :locale-definition="localeDefinition"
      />
    </template>
    <template v-else-if="reportType === 'counter'">
      <counter-report
        :report-response="reportResponse"
        :locale-definition="localeDefinition"
      />
    </template>
    <template v-else-if="reportType === 'pie'">
      <pie-report
        :report-response="reportResponse"
        :locale-definition="localeDefinition"
      />
    </template>
    <template v-else-if="reportType === 'table'">
      <table-report
        :report-response="reportResponse"
        :locale-definition="localeDefinition"
      />
    </template>
    <template v-else-if="reportType === 'geo'">
      <geo-report
        :report-response="reportResponse"
        :locale-definition="localeDefinition"
      />
    </template>
  </div>
</template>

<script>
import AreaReport from '@/js/components/widget-reports/AreaReport';
import CounterReport from '@/js/components/widget-reports/CounterReport';
import PieReport from '@/js/components/widget-reports/PieReport';
import TableReport from '@/js/components/widget-reports/TableReport';
import GeoReport from '@/js/components/widget-reports/GeoReport';

export default {
  components: {GeoReport, TableReport, PieReport, CounterReport, AreaReport},
  props: {
    reportResponse: {
      type: Object,
      required: true,
    },
    localeDefinition: {
      type: Object,
      required: true,
    },
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