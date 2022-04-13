<template>
  <div>
    <h2>Report Widget</h2>
    <div class="da-grid da-grid-cols-2 da-gap-6">
      <div>
        <report-widget :request-criteria="requestCriteria" />
      </div>
      <div>
        <report-widget-settings @update-criteria="updateRequestCriteria" />
      </div>
    </div>
    #{{ requestCriteria }}#
  </div>
</template>
<script>
import ReportWidget from '@/js/ReportWidget';
import ReportWidgetSettings from '@/js/ReportWidgetSettings';
export default {
  components: {ReportWidgetSettings, ReportWidget},

  data() {
    return {
      requestCriteria: null,
    }
  },

  methods: {
    updateRequestCriteria(event) {
      console.log('updateRequestCriteria', event);
      this.requestCriteria = event
    }
  }
}
</script>