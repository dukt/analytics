<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2019, Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\apis;

use Craft;
use dukt\analytics\base\Api;
use dukt\analytics\Plugin;
use \Google_Service_Analytics;
use \Google_Service_Analytics_Columns;

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

    /**
     * Get columns.
     *
     * @return Google_Service_Analytics_Columns
     * @throws \yii\base\InvalidConfigException
     */
    public function getColumns()
    {
        return Plugin::$plugin->getApis()->getAnalytics()->getService()->metadata_columns->listMetadataColumns('ga');
    }

    /**
     * Get account explorer data.
     *
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function getAccountExplorerData()
    {
        $apiAccounts = Plugin::$plugin->getApis()->getAnalytics()->getService()->management_accounts->listManagementAccounts();
        $accounts = $apiAccounts->toSimpleObject()->items;

        $apiProperties = Plugin::$plugin->getApis()->getAnalytics()->getService()->management_webproperties->listManagementWebproperties('~all');
        $properties = $apiProperties->toSimpleObject()->items;

        $apiViews = Plugin::$plugin->getApis()->getAnalytics()->getService()->management_profiles->listManagementProfiles('~all', '~all');
        $views = $apiViews->toSimpleObject()->items;

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
     * @throws \yii\base\InvalidConfigException
     */
    public function populateAccountExplorerSettings($settings = [])
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

    /**
     * Parse Report Response
     *
     * @param $data
     *
     * @return array
     */
    public function parseReportResponse($data): array
    {
        $cols = $this->parseReportResponseCols($data);
        $rows = $this->parseReportResponseRows($data, $cols);

        return [
            'cols' => $cols,
            'rows' => $rows
        ];
    }

    // Private Methods
    // =========================================================================

    /**
     * @param array $data
     * @return array
     */
    private function parseReportResponseCols(array $data): array
    {
        $cols = [];

        foreach ($data['columnHeaders'] as $col) {
            $dataType = $col->dataType;
            $id = $col->name;
            $label = Plugin::$plugin->metadata->getDimMet($col->name);
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

        return $cols;
    }

    /**
     * @param array $data
     * @param array $cols
     * @return array
     */
    private function parseReportResponseRows(array $data, array $cols): array
    {
        $rows = [];

        if ($data['rows']) {
            $rows = $data->rows;

            foreach ($rows as $kRow => $row) {
                foreach ($row as $_valueKey => $_value) {
                    $col = $cols[$_valueKey];

                    $value = $this->formatRawValue($col['type'], $_value);

                    if ($col['id'] == 'ga:continent') {
                        $value = Plugin::$plugin->geo->getContinentCode($value);
                    }

                    if ($col['id'] == 'ga:subContinent') {
                        $value = Plugin::$plugin->geo->getSubContinentCode($value);
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

        return $rows;
    }

    /**
     * Format RAW value
     *
     * @param string $type
     * @param string $value
     *
     * @return float|string
     */
    private function formatRawValue($type, $value)
    {
        switch ($type) {
            case 'integer':
            case 'currency':
            case 'float':
            case 'time':
            case 'percent':
                return (float)$value;
                break;

            default:
                return (string)$value;
        }
    }
}
