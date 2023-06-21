<template>
  <div class="da-grid da-grid-cols-2 da-gap-6">
    <area-report />
    <geo-report />
    <counter-report />
    <pie-report />
    <table-report />
  </div>
</template>
<script>
import GeoReport from '@/js/components/test-reports/GeoReport.vue';
import AreaReport from '@/js/components/test-reports/AreaReport.vue';
import PieReport from '@/js/components/test-reports/PieReport.vue';
import TableReport from '@/js/components/test-reports/TableReport.vue';
import CounterReport from '@/js/components/test-reports/CounterReport.vue';
export default {
  components: {GeoReport, CounterReport, TableReport, PieReport, AreaReport}
}
</script>