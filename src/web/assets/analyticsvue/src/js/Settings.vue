<template>
  <div>
    <div
      class="da-flex da-border da-rounded-md"
    >
      <div
        class="da-flex-1 da-border-r"
      >
        <div
          class="da-border-b da-font-medium da-text-gray-500 da-px-6 da-py-3"
        >
          Analytics Accounts
        </div>
        <div>
          <ul>
            <template v-for="(account, accountKey) in accounts">
              <li
                :key="accountKey"
              >
                <button
                  class="da-px-6 da-py-3 da-block da-w-full da-text-left"
                  :class="{
                    'da-bg-gray-200': selectedAccount === account.id,
                    'hover:da-bg-gray-100': selectedAccount !== account.id,
                  }"
                  @click.prevent="selectedAccount = account.id"
                >
                  {{ account.name }}
                  <div
                    class="da-text-gray-500"
                  >
                    {{ account.id }}
                  </div>
                </button>
              </li>
            </template>
          </ul>
        </div>
      </div>
      <div
        class="da-flex-1 da-border-r"
      >
        <div
          class="da-border-b da-font-medium da-text-gray-500 da-px-6 da-py-3"
        >
          Properties &amp; Apps
        </div>
        <div>
          <ul>
            <template v-for="(property, propertyKey) in properties">
              <li
                :key="propertyKey"
              >
                <button
                  class="da-px-6 da-py-3 da-block da-w-full da-text-left"
                  :class="{
                    'da-bg-gray-200': selectedProperty === property.id,
                    'hover:da-bg-gray-100': selectedProperty !== property.id,
                  }"
                  @click.prevent="selectedProperty = property.id"
                >
                  {{ property.name }}
                  <div
                    class="da-text-gray-500"
                  >
                    {{ property.id }}
                  </div>
                </button>
              </li>
            </template>
          </ul>
        </div>
      </div>
      <div
        class="da-flex-1"
      >
        <div
          class="da-border-b da-font-medium da-text-gray-500 da-px-6 da-py-3"
        >
          Views
        </div>
        <div>
          <ul>
            <template v-for="(view, viewKey) in views">
              <li
                :key="viewKey"
                class="da-px-6 da-py-3"
              >
                {{ view.name }}

                <button
                  class="da-bg-blue-600 da-text-white da-font-medium da-rounded-md da-px-2 da-py-1 da-ml-2"
                >
                  Select
                </button>
              </li>
            </template>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import settingsApi from './api/settings'

export default {
  data() {
      return {
        accountExplorerData: null,
        selectedAccount: null,
        selectedProperty: null,
      }
  },

  computed: {
    accounts() {
      if (!this.accountExplorerData) {
        return []
      }

      return this.accountExplorerData.accounts
    },
    properties() {
      if (!this.accountExplorerData) {
        return []
      }

      if (!this.selectedAccount) {
        return []
      }

      return this.accountExplorerData.properties.filter(property => property.accountId === this.selectedAccount)
    },
    views() {
      if (!this.accountExplorerData) {
        return []
      }

      if (!this.selectedProperty) {
        return []
      }

      return this.accountExplorerData.views.filter(view => view.webPropertyId === this.selectedProperty)
    },
  },

  mounted() {
    settingsApi.getAccountExplorerData()
      .then(response => {
        this.accountExplorerData = response.data;
      });
  }
}
</script>