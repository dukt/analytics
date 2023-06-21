<template>
  <div>
    <div
      class="da-font-bold"
    >
      {{ reportResponse.data.metric }}
    </div>
    <div>{{ reportResponse.data.periodLabel }}</div>
    <div
      class="da-text-gray-500"
    >
      {{ reportResponse.data.view }}
    </div>
  </div>
</template>

<script>
export default {
  props: {
    reportResponse: {
      type: Object,
      required: true,
    },
  },
}
</script>