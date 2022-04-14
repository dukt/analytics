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
import GeoReport from '@/js/components/test-reports/GeoReport';
import AreaReport from '@/js/components/test-reports/AreaReport';
import PieReport from '@/js/components/test-reports/PieReport';
import TableReport from '@/js/components/test-reports/TableReport';
import CounterReport from '@/js/components/test-reports/CounterReport';
export default {
  components: {GeoReport, CounterReport, TableReport, PieReport, AreaReport}
}
</script>