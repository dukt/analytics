<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\services;

use Craft;
use dukt\analytics\base\Api;
use dukt\analytics\models\RequestCriteria;
use dukt\analytics\Plugin as Analytics;
use \Google_Service_Analytics;
use \Google_Service_Analytics_Columns;

class AnalyticsApi extends Api
{
    // Public Methods
    // =========================================================================

    /**
     * Get columns.
     *
     * @return Google_Service_Analytics_Columns
     */
    public function getColumns()
    {
        return $this->googleAnalytics()->metadata_columns->listMetadataColumns('ga');
    }

    /**
     * Get account explorer data.
     *
     * @return array
     */
    public function getAccountExplorerData()
    {
        // Accounts
        $apiAccounts = $this->googleAnalytics()->management_accounts->listManagementAccounts();
        $accounts = $apiAccounts->toSimpleObject()->items;

        // Properties
        $apiProperties = $this->googleAnalytics()->management_webproperties->listManagementWebproperties('~all');;
        $properties = $apiProperties->toSimpleObject()->items;

        // Views
        $apiViews = $this->googleAnalytics()->management_profiles->listManagementProfiles('~all', '~all');
        $views = $apiViews->toSimpleObject()->items;

        // Return Data
        return [
            'accounts' => $accounts,
            'properties' => $properties,
            'views' => $views,
        ];
    }

    /**
     * Populate Account Explorer Settings
     *
     * @param array $settings
     *
     * @return array
     */
    public function populateAccountExplorerSettings($settings = [])
    {
        if (!empty($settings['accountId']) && !empty($settings['webPropertyId']) && !empty($settings['profileId'])) {
            $apiAccounts = $this->googleAnalytics()->management_accounts->listManagementAccounts();

            $account = null;

            foreach ($apiAccounts as $apiAccount) {
                if ($apiAccount->id == $settings['accountId']) {
                    $account = $apiAccount;
                }
            }

            $webProperty = $this->googleAnalytics()->management_webproperties->get($settings['accountId'], $settings['webPropertyId']);
            $profile = $this->googleAnalytics()->management_profiles->get($settings['accountId'], $settings['webPropertyId'], $settings['profileId']);

            $settings['accountName'] = $account->name;
            $settings['webPropertyName'] = $webProperty->name;
            $settings['internalWebPropertyId'] = $webProperty->internalWebPropertyId;
            $settings['profileCurrency'] = $profile->currency;
            $settings['profileName'] = $profile->name;
        }

        return $settings;
    }

    /**
     * Parse Report Response
     *
     * @param $data
     *
     * @return array
     */
    public function parseReportResponse($data)
    {
        // Columns

        $cols = [];

        foreach ($data['columnHeaders'] as $col) {
            $dataType = $col->dataType;
            $id = $col->name;
            $label = Analytics::$plugin->metadata->getDimMet($col->name);
            $type = strtolower($dataType);

            switch ($col->name) {
                case 'ga:date':
                case 'ga:yearMonth':
                    $type = 'date';
                    break;

                case 'ga:continent':
                    $type = 'continent';
                    break;
                case 'ga:subContinent':
                    $type = 'subContinent';
                    break;

                case 'ga:latitude':
                case 'ga:longitude':
                    $type = 'float';
                    break;
            }

            $cols[] = [
                'type' => $type,
                'dataType' => $dataType,
                'id' => $id,
                'label' => Craft::t('analytics', $label),
            ];
        }


        // Rows

        $rows = [];

        if ($data['rows']) {
            $rows = $data->rows;

            foreach ($rows as $kRow => $row) {
                foreach ($row as $_valueKey => $_value) {
                    $col = $cols[$_valueKey];

                    $value = $this->formatRawValue($col['type'], $_value);

                    if ($col['id'] == 'ga:continent') {
                        $value = Analytics::$plugin->metadata->getContinentCode($value);
                    }

                    if ($col['id'] == 'ga:subContinent') {
                        $value = Analytics::$plugin->metadata->getSubContinentCode($value);
                    }


                    // translate values

                    switch ($col['id']) {
                        case 'ga:country':
                        case 'ga:city':
                            // case 'ga:continent':
                            // case 'ga:subContinent':
                        case 'ga:userType':
                        case 'ga:javaEnabled':
                        case 'ga:deviceCategory':
                        case 'ga:mobileInputSelector':
                        case 'ga:channelGrouping':
                        case 'ga:medium':
                            $value = Craft::t('analytics', $value);
                            break;
                    }


                    // update cell

                    $rows[$kRow][$_valueKey] = $value;
                }
            }
        }

        return [
            'cols' => $cols,
            'rows' => $rows
        ];
    }

    /**
     * Returns the Google Analytics API object (API v3)
     *
     * @return bool|Google_Service_Analytics
     */
    public function googleAnalytics()
    {
        $client = $this->getClient();

        return new Google_Service_Analytics($client);
    }

    // Private Methods
    // =========================================================================

    /**
     * Format RAW value
     *
     * @param string $type
     * @param string $value
     */
    private function formatRawValue($type, $value)
    {
        switch ($type) {
            case 'integer':
            case 'currency':
            case 'float':
            case 'time':
            case 'percent':
                $value = (float)$value;
                break;

            default:
                $value = (string)$value;
        }

        return $value;
    }
}
