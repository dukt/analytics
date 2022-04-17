<template>
  <div
    v-if="reportResponse.data"
  >
    <div class="da-text-center">
      <div class="da-text-7xl">
        {{ counterData.value }}
      </div>
      <div class="da-mt-2">
        <span class="da-text-blue-600">{{ reportResponse.data.metric }}</span>
        {{ reportResponse.data.periodLabel }}
      </div>
      <div
        class="da-text-gray-500"
      >
        {{ reportResponse.data.view }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    reportResponse: {
      type: Object,
      required: true
    }
  },
  computed: {
    counterData() {
      if (!this.reportResponse || !this.reportResponse.data || !this.reportResponse.data.counter) {
        return null
      }

      return this.reportResponse.data.counter
    }
  }
}
</script>