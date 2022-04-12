<template>
  <div>
    <h2>Report Widget</h2>
    <div class="da-grid da-grid-cols-2 da-gap-6">
      <div>
        <report-widget />
      </div>
      <div>
        <report-widget-settings />
      </div>
    </div>
  </div>
</template>
<script>
import ReportWidget from '@/js/ReportWidget';
import ReportWidgetSettings from '@/js/ReportWidgetSettings';
export default {
  components: {ReportWidgetSettings, ReportWidget}
}
</script>