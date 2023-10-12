<template>
  <div>
    <template v-if="loading">
      <div>
        Loading…
      </div>
    </template>
    <template v-else>
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
          <div class="da-max-h-[22rem] da-overflow-auto">
            <ul>
              <template v-for="(account, accountKey) in accounts">
                <li
                  :key="accountKey"
                >
                  <button
                    class="da-px-6 da-py-3 da-block da-w-full da-text-left da-flex da-justify-between da-items-center"
                    :class="{
                      'da-bg-gray-100': selectedAccount === account.id,
                      'hover:da-bg-gray-100/50': selectedAccount !== account.id,
                    }"
                    @click.prevent="selectedAccount = account.id"
                  >
                    <div>
                      <div>
                        {{ account.name }}
                      </div>
                      <div
                        class="da-text-gray-500"
                      >
                        {{ account.id }}
                      </div>
                    </div>

                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="da-h-4 da-w-4 da-text-gray-700"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                      stroke-width="2"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M9 5l7 7-7 7"
                      />
                    </svg>
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
          <div class="da-max-h-[22rem] da-overflow-auto">
            <ul>
              <template v-for="(property, propertyKey) in properties">
                <li
                  :key="propertyKey"
                >
                  <template v-if="property.type === 'UA'">
                    <button
                      class="da-px-6 da-py-3 da-block da-w-full da-text-left"
                      :class="{
                        'da-bg-gray-100': selectedProperty === property.id,
                        'hover:da-bg-gray-100/50': selectedProperty !== property.id,
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
                  </template>
                  <template v-else>
                    <div
                      class="da-px-6 da-py-3 da-flex da-justify-between"
                      :class="{ 'da-bg-gray-100': selectedProperty === property.id }"
                    >
                      <div>
                        {{ property.name }}
                        <div
                          class="da-text-gray-500"
                        >
                          {{ property.id }}
                        </div>
                      </div>
                      <div>
                        <button
                          class="da-bg-blue-600 da-text-white da-font-medium da-rounded-md da-px-2 da-py-1 da-ml-2"
                          :class="{
                            'da-opacity-50': selectedProperty === property.id,
                          }"
                          :disabled="selectedProperty === property.id"
                          @click.prevent="selectGA4Property(property)"
                        >
                          Select GA4 property
                        </button>
                      </div>
                    </div>
                  </template>
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
          <div class="da-max-h-[22rem] da-overflow-auto">
            <ul>
              <template v-for="(view, viewKey) in views">
                <li
                  :key="viewKey"
                  class="da-px-6 da-py-3"
                  :class="{ 'da-bg-gray-100': selectedView === view.id }"
                >
                  {{ view.name }} ({{ view.id }})

                  <button
                    class="da-bg-blue-600 da-text-white da-font-medium da-rounded-md da-px-2 da-py-1 da-ml-2"
                    :class="{
                      'da-opacity-50': selectedView === view.id,
                    }"
                    :disabled="selectedView === view.id"
                    @click.prevent="selectUAView(view)"
                  >
                    Select UA view
                  </button>
                </li>
              </template>
            </ul>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import settingsApi from './api/settings'

export default {
  data() {
    return {
      loading: false,
      accountExplorerData: null,
      selectedSourceType: null,
      selectedAccount: null,
      selectedProperty: null,
      selectedView: null,
      selectedCurrency: null,
      pluginOptions: null,
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
    this.loading = true

    settingsApi.getAccountExplorerData()
      .then(response => {
        this.loading = false
        this.accountExplorerData = response.data;
      });

    this.selectedAccount = this.pluginOptions.source.gaAccountId
    this.selectedProperty = this.pluginOptions.source.gaPropertyId
    this.selectedView = this.pluginOptions.source.gaViewId
  },

  methods: {
    selectUAView(view) {
      this.selectedSourceType = 'UA'
      this.selectedView = view.id
      // const selectedProperty = this.properties.find(property => property.id === this.selectedProperty)

      this.setFormData()
    },
    selectGA4Property(property) {
      console.log('property', property)

      this.selectedSourceType = 'GA4'
      this.selectedProperty = property.id
      this.selectedView = null

      this.setFormData()
    },

    setFormData() {
      document.getElementById('accountExplorerType').value = this.selectedSourceType
      document.getElementById('accountExplorerAccount').value = this.selectedAccount
      document.getElementById('accountExplorerProperty').value = this.selectedProperty

      if (this.selectedSourceType === 'UA') {
        document.getElementById('accountExplorerView').value = this.selectedView
      } else {
        document.getElementById('accountExplorerView').value = ''
      }

      document.getElementById('accountExplorerCurrency').value = ''
    }
  }
}
</script>