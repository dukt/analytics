<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\apis;

use dukt\analytics\base\Api;
use dukt\analytics\Plugin;
use Google\Service\AnalyticsData;
use \Google_Service_Analytics;
use \Google\Service\GoogleAnalyticsAdmin;

class Analytics extends Api
{
    // Public Methods
    // =========================================================================

    /**
     * @return Google_Service_Analytics
     * @throws \yii\base\InvalidConfigException
     */
    public function getService()
    {
        $client = $this->getClient();

        return new Google_Service_Analytics($client);
    }

    public function getGoogleAdminService()
    {
        $client = $this->getClient();

        return new GoogleAnalyticsAdmin($client);
    }

    public function getAnalyticsData()
    {
        $client = $this->getClient();

        return new AnalyticsData($client);
    }

    /**
     * Get account explorer data.
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getAccountExplorerData(): array
    {
        return [
            'accounts' => $this->getAllAccounts(),
            'properties' => $this->getAllProperties(),
        ];
    }

    /**
     * Populate Account Explorer Settings
     *
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     * @param mixed[] $settings
     */
    public function populateAccountExplorerSettings(array $settings = [])
    {
        if (!empty($settings['accountId']) && !empty($settings['webPropertyId']) && !empty($settings['profileId'])) {
            $apiAccounts = Plugin::$plugin->getApis()->getAnalytics()->getService()->management_accounts->listManagementAccounts();

            $account = null;

            foreach ($apiAccounts as $apiAccount) {
                if ($apiAccount->id == $settings['accountId']) {
                    $account = $apiAccount;
                }
            }

            $webProperty = Plugin::$plugin->getApis()->getAnalytics()->getService()->management_webproperties->get($settings['accountId'], $settings['webPropertyId']);
            $profile = Plugin::$plugin->getApis()->getAnalytics()->getService()->management_profiles->get($settings['accountId'], $settings['webPropertyId'], $settings['profileId']);

            $settings['accountName'] = $account->name;
            $settings['webPropertyName'] = $webProperty->name;
            $settings['internalWebPropertyId'] = $webProperty->internalWebPropertyId;
            $settings['profileCurrency'] = $profile->currency;
            $settings['profileName'] = $profile->name;
        }

        return $settings;
    }

    // Private Methods
    // =========================================================================

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    private function getAllAccounts(): array
    {
        $startIndex = 1;
        $maxResults = 1000;
        $managementAccounts = Plugin::$plugin->getApis()->getAnalytics()->getService()->management_accounts;

        $response = $managementAccounts->listManagementAccounts([
            'start-index' => $startIndex,
            'max-results' => $maxResults,
        ]);

        $totalResults = (int) $response->totalResults;

        if ($totalResults === 0) {
            return [];
        }

        $items = [];
        $index = 0;

        $items[] = $response->toSimpleObject()->items;
        $index += is_array($response->toSimpleObject()->items) || $response->toSimpleObject()->items instanceof \Countable ? count($response->toSimpleObject()->items) : 0;

        while($index < $totalResults) {
            $startIndex = $index + 1;

            $response = $managementAccounts->listManagementAccounts([
                'start-index' => $startIndex,
                'max-results' => $maxResults,
            ]);

            $items[] = $response->toSimpleObject()->items;
            $index += is_array($response->toSimpleObject()->items) || $response->toSimpleObject()->items instanceof \Countable ? count($response->toSimpleObject()->items) : 0;
        }

        return array_merge(...$items);
    }

    /**
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    private function getAllProperties(): array
    {
        // GA4 Properties

        $googleAdminService = Plugin::$plugin->getApis()->getAnalytics()->getGoogleAdminService();
        $accountSummaries = $googleAdminService->accountSummaries->listAccountSummaries()->getAccountSummaries();

        foreach($accountSummaries as $accountSummary) {
            // We need to get the actual list of properties to get all property details (like currency)
            $props = $googleAdminService->properties->listProperties([
                'filter' => 'parent:' . $accountSummary->account
            ])->getProperties();

            $items[] = array_map(function($item) {
                return [
                    'type' => 'GA4',
                    'id' => $item->getName(),
                    'name' => $item->getDisplayName(),
                    'accountId' => str_replace('accounts/', '', $item->getParent()),
                    'currency' => $item->getCurrencyCode(),
                ];
            }, $props);
        }

        return array_merge(...$items);
    }
}
