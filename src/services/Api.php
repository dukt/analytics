<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\services;

use Craft;
use yii\base\Component;
use \Google_Client;
use \Google_Service_Analytics;
use \Google_Service_AnalyticsReporting;
use dukt\analytics\models\RequestCriteria;
use dukt\analytics\Plugin as Analytics;
use \Google_Service_AnalyticsReporting_ReportRequest;
use \Google_Service_AnalyticsReporting_DateRange;
use \Google_Service_AnalyticsReporting_Metric;
use \Google_Service_AnalyticsReporting_Dimension;
use \Google_Service_AnalyticsReporting_GetReportsRequest;
use \Google_Service_AnalyticsReporting_GetReportsResponse;

class Api extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * Get columns
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->googleAnalytics()->metadata_columns->listMetadataColumns('ga');
    }

    /**
     * Get Account Explorer Data
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
    public function populateAccountExplorerSettings($settings = array())
    {
        if(!empty($settings['accountId']) && !empty($settings['webPropertyId']) && !empty($settings['profileId']))
        {
            $apiAccounts = $this->googleAnalytics()->management_accounts->listManagementAccounts();

            $account = null;

            foreach($apiAccounts as $apiAccount)
            {
                if($apiAccount->id == $settings['accountId'])
                {
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
     * Sends a request based on RequestCriteria to Google Analytics' API.
     *
     * @param RequestCriteria $criteria
     *
     * @return string
     */
    public function sendRequest(RequestCriteria $criteria)
    {
        if($criteria->realtime)
        {
            return $this->getRealtimeReportFromCriteria($criteria);
        }
        else
        {
            return $this->getReportFromCriteria($criteria);
        }
    }

    public function getReportNew(RequestCriteria $criteria)
    {
        // $this->populateCriteria($criteria);


        $startDate = Craft::$app->getRequest()->getParam('startDate');
        $endDate = Craft::$app->getRequest()->getParam('endDate');
        $_metrics = Craft::$app->getRequest()->getParam('metrics');
        $_dimensions = Craft::$app->getRequest()->getParam('dimensions');

        $viewId = Analytics::$plugin->getAnalytics()->getProfileId();

        $dateRange = Analytics::$plugin->getApi()->getAnalyticsReportingDateRange($startDate, $endDate);
        $metrics = Analytics::$plugin->getApi()->getMetricsFromString($_metrics);
        $dimensions = Analytics::$plugin->getApi()->getDimensionsFromString($_dimensions);

        // Request
        // $request = Analytics::$plugin->getApi()->getAnalyticsReportingReportRequest($viewId, $dateRange, $metrics, $dimensions);
        $request = new Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($viewId);
        $request->setDateRanges($dateRange);
        $request->setMetrics($metrics);
        $request->setDimensions($dimensions);
        $request->setOrderBys([
            [
                "fieldName" => $metrics[0],
                "orderType" => 'VALUE',
                "sortOrder" => 'DESCENDING',
            ]
        ]);
        $request->setPageSize(20);
        $request->setFiltersExpression($dimensions[0].'!=(not set);'.$dimensions[0].'!=(not provided)');

        $requests = Analytics::$plugin->getApi()->getAnalyticsReportingGetReportsRequest(array($request));
        $response = Analytics::$plugin->getApi()->getAnalyticsReporting()->reports->batchGet($requests);
        $reports = Analytics::$plugin->getApi()->parseReportsResponseApiV4($response);

        Craft::$app->getUrlManager()->setRouteParams([
            'response' => $response,
            'reports' => $reports,
        ]);
    }


    /**
     * Parse Report Response
     *
     * @param $data
     *
     * @return array
     */
    public function parseReportResponseApiV3($data)
    {
        // Columns

        $cols = [];

        foreach($data['columnHeaders'] as $col)
        {
            $dataType = $col->dataType;
            $id = $col->name;
            $label = Analytics::$plugin->metadata->getDimMet($col->name);
            $type = strtolower($dataType);

            switch($col->name)
            {
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

            $cols[] = array(
                'type' => $type,
                'dataType' => $dataType,
                'id' => $id,
                'label' => Craft::t('analytics', $label),
            );
        }


        // Rows

        $rows = [];

        if($data['rows'])
        {
            $rows = $data->rows;

            foreach($rows as $kRow => $row)
            {
                foreach($row as $_valueKey => $_value)
                {
                    $col = $cols[$_valueKey];

                    $value = $this->formatRawValue($col['type'], $_value);

                    if($col['id'] == 'ga:continent')
                    {
                        $value = Analytics::$plugin->metadata->getContinentCode($value);
                    }

                    if($col['id'] == 'ga:subContinent')
                    {
                        $value = Analytics::$plugin->metadata->getSubContinentCode($value);
                    }


                    // translate values

                    switch($col['id'])
                    {
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

        return array(
            'cols' => $cols,
            'rows' => $rows
        );
    }

    public function parseReportsResponseApiV4(Google_Service_AnalyticsReporting_GetReportsResponse $response)
    {
        $reports = [];

        foreach ($response->getReports() as $_report) {


            $columnHeader = $_report->getColumnHeader();
            $columnHeaderDimensions = $columnHeader->getDimensions();
            $metricHeaderEntries = $columnHeader->getMetricHeader()->getMetricHeaderEntries();


            // Columns

            $cols = [];

            if($columnHeaderDimensions) {
                foreach ($columnHeaderDimensions as $columnHeaderDimension) {

                    $id = $columnHeaderDimension;
                    $label = Analytics::$plugin->metadata->getDimMet($columnHeaderDimension);

                    switch ($columnHeaderDimension) {
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

                        default:
                            $type = 'string';
                    }

                    $col = [
                        'type' => $type,
                        'label' => Craft::t('analytics', $label),
                        'id' => $id,
                    ];

                    array_push($cols, $col);
                }
            }

            foreach($metricHeaderEntries as $metricHeaderEntry) {
                $label = Analytics::$plugin->metadata->getDimMet($metricHeaderEntry['name']);

                $col = [
                    'type' => strtolower($metricHeaderEntry['type']),
                    'label' => Craft::t('analytics', $label),
                    'id' => $metricHeaderEntry['name'],
                ];

                array_push($cols, $col);
            }


            // Rows

            $rows = [];

            foreach($_report->getData()->getRows() as $_row) {

                $colIndex = 0;
                $row = [];

                $dimensions = $_row->getDimensions();

                if($dimensions) {
                    foreach ($dimensions as $_dimension) {

                        $value = $_dimension;

                        if($columnHeaderDimensions) {
                            if(isset($columnHeaderDimensions[$colIndex])) {
                                switch($columnHeaderDimensions[$colIndex])
                                {
                                    case 'ga:continent':
                                        $value = Analytics::$plugin->metadata->getContinentCode($value);
                                        break;
                                    case 'ga:subContinent':
                                        $value = Analytics::$plugin->metadata->getSubContinentCode($value);
                                        break;
                                }
                            }
                        }

                        array_push($row, $value);

                        $colIndex++;
                    }
                }

                foreach($_row->getMetrics() as $_metric) {
                    array_push($row, $_metric->getValues()[0]);
                    $colIndex++;
                }

                array_push($rows, $row);
            }

            $totals = $_report->getData()->getTotals()[0]->getValues();

            $report = [
                'cols' => $cols,
                'rows' => $rows,
                'totals' => $totals
            ];

            array_push($reports, $report);
        }

        return $reports;
    }

    public function getAnalyticsReportingGetReportsRequest($requests)
    {
        $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
        $body->setReportRequests($requests);

        return $body;
    }

    public function getAnalyticsReportingReportRequest($viewId, $dateRanges, $metrics, $dimensions)
    {
        $request = new Google_Service_AnalyticsReporting_ReportRequest();
        $request->setViewId($viewId);
        $request->setDateRanges($dateRanges);
        $request->setMetrics($metrics);
        $request->setDimensions($dimensions);

        return $request;
    }

    public function getDimensionsFromString($string)
    {
        $dimensions = [];
        $_dimensions = explode(",", $string);
        foreach ($_dimensions as $_dimension) {
            $dimension = new Google_Service_AnalyticsReporting_Dimension();
            $dimension->setName($_dimension);
            array_push($dimensions, $dimension);
        }

        return $dimensions;
    }

    public function getMetricsFromString($string)
    {
        $metrics = [];
        $_metrics = explode(",", $string);
        foreach ($_metrics as $_metric) {
            $metric = new Google_Service_AnalyticsReporting_Metric();
            $metric->setExpression($_metric);
            array_push($metrics, $metric);
        }

        return $metrics;
    }

    public function getAnalyticsReportingDateRange($startDate, $endDate)
    {
        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        $dateRange->setStartDate($startDate);
        $dateRange->setEndDate($endDate);

        return $dateRange;
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
    
    /**
     * Returns the Google Analytics Reporting API object (API v4)
     *
     * @return bool|Google_Service_AnalyticsReporting
     */
    public function getAnalyticsReporting()
    {
        $client = $this->getClient();

        return new Google_Service_AnalyticsReporting($client);
    }

    // Private Methods
    // =========================================================================

    /**
     * Populates criteria’s ids, optParams.filters attributes with default profileId and filters
     *
     * @param RequestCriteria $criteria
     */
    private function populateCriteria(RequestCriteria $criteria)
    {
        // Profile ID

        $criteria->ids = Analytics::$plugin->getAnalytics()->getProfileId();


        // Filters

        $filters = [];

        if(isset($criteria->optParams['filters']))
        {
            $filters = $criteria->optParams['filters'];

            if(is_string($filters))
            {
                $filters = explode(";", $filters);
            }
        }

        $configFilters = Analytics::$plugin->getSettings()->filters;

        if($configFilters)
        {
            $filters = array_merge($filters, $configFilters);
        }

        if(count($filters) > 0)
        {
            $optParams = $criteria->optParams;

            $optParams['filters'] = implode(";", $filters);

            $criteria->optParams = $optParams;
        }
    }

    /**
     * Returns a GA Report from criteria
     *
     * @param RequestCriteria $criteria
     *
     * @return array
     */
    private function getReportFromCriteria(RequestCriteria $criteria)
    {
        $this->populateCriteria($criteria);

        $ids = $criteria->ids;
        $startDate = $criteria->startDate;
        $endDate = $criteria->endDate;
        $metrics = $criteria->metrics;
        $optParams = $criteria->optParams;
        $enableCache = $criteria->enableCache;

        $request = [$ids, $startDate, $endDate, $metrics, $optParams];

        $cacheId = ['api.apiGetGAData', $request];
        $response = Analytics::$plugin->cache->get($cacheId);

        if(!$response)
        {
            if(!$optParams)
            {
                $optParams = [];
            }

            $response = $this->googleAnalytics()->data_ga->get($ids, $startDate, $endDate, $metrics, $optParams);
            Analytics::$plugin->cache->set($cacheId, $response, null, null, $enableCache);
        }

        return $this->parseReportResponseApiV3($response);
    }

    /**
     * Returns a Realtime Report from criteria
     *
     * @param RequestCriteria $criteria
     *
     * @return array
     */
    private function getRealtimeReportFromCriteria(RequestCriteria $criteria)
    {
        $this->populateCriteria($criteria);

        $ids = $criteria->ids;
        $metrics = $criteria->metrics;
        $optParams = $criteria->optParams;

        $cacheDuration = Analytics::$plugin->getAnalytics()->getRealtimeRefreshInterval();

        $cacheId = ['api.apiGetGADataRealtime', $ids, $metrics, $optParams];
        $response = Analytics::$plugin->cache->get($cacheId);

        if(!$response)
        {
            $response = $this->googleAnalytics()->data_realtime->get($ids, $metrics, $optParams);

            Analytics::$plugin->cache->set($cacheId, $response, $cacheDuration);
        }

        return $this->parseReportResponseApiV3($response);
    }

    /**
     * Format RAW value
     *
     * @param string $type
     * @param string $value
     */
    private function formatRawValue($type, $value)
    {
        switch($type)
        {
            case 'integer':
            case 'currency':
            case 'float':
            case 'time':
            case 'percent':
                $value = (float) $value;
                break;

            default:
                $value = (string) $value;
        }

        return $value;
    }

    /**
     * Returns a Google client
     *
     * @return null|Google_Client
     */
    private function getClient()
    {
        $token = Analytics::$plugin->getOauth()->getToken();

        if ($token) {
            // make token compatible with Google library
            $arrayToken = [
                'created' => 0,
                'access_token' => $token->getToken(),
                'expires_in' => $token->getExpires(),
            ];

            $arrayToken = json_encode($arrayToken);

            // client
            $client = new Google_Client();
            $client->setApplicationName('Google+ PHP Starter Application');
            $client->setClientId('clientId');
            $client->setClientSecret('clientSecret');
            $client->setRedirectUri('redirectUri');
            $client->setAccessToken($arrayToken);

            return $client;
        }
    }
}
