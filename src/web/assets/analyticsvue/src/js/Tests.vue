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
import GeoReport from '@/js/components/reports/GeoReport';
import AreaReport from '@/js/components/reports/AreaReport';
import PieReport from '@/js/components/reports/PieReport';
import TableReport from '@/js/components/reports/TableReport';
import CounterReport from '@/js/components/reports/CounterReport';
export default {
  components: {GeoReport, CounterReport, TableReport, PieReport, AreaReport}
}
</script>