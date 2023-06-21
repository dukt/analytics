<template>
  <div>
    <h2>Report Widget</h2>
    <div class="da-grid da-grid-cols-2 da-gap-6">
      <div>
        <report-widget-settings @update-criteria="updateRequestCriteria" />
      </div>
      <div>
        <report-widget :request-criteria="requestCriteria" />
        <div class="da-mt-6">
          <pre>{{ requestCriteria }}</pre>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import ReportWidget from '@/js/tests/ReportWidget.vue';
import ReportWidgetSettings from '@/js/tests/ReportWidgetSettings.vue';

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