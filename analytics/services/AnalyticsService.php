<?php

/**
 * Craft Analytics by Dukt
 *
 * @package   Craft Analytics
 * @author    Benjamin David
 * @copyright Copyright (c) 2014, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 * @link      https://dukt.net/craft/analytics/
 */

namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'analytics/vendor/autoload.php');

use \Google_Client;
use \Google_Service_Analytics;

class AnalyticsService extends BaseApplicationComponent
{
    public function api($options)
    {
        try
        {
            $profile = craft()->analytics->getProfile();

            $response = array(
                'cols' => array(),
                'rows' => array(),
                'success' => false,
                'error' => false
            );

            $ids = 'ga:'.$profile['id'];
            $start = null;
            $end = null;
            $metrics = null;

            if(isset($options['start-date']))
            {
                $start = $options['start-date'];
                unset($options['start-date']);
            }

            if(isset($options['start-date']))
            {
                $end = $options['end-date'];
                unset($options['end-date']);
            }

            if(isset($options['start-date']))
            {
                $metrics = $options['metrics'];
                unset($options['metrics']);
            }

            // request

            $apiResponse = null;
            $enableCache = true;

            if(craft()->config->get('disableCache', 'analytics') == true)
            {
                $enableCache = false;
            }

            if($enableCache)
            {
                $cacheKey = 'analytics/template/'.md5(serialize(array(
                    $ids,
                    $start,
                    $end,
                    $metrics,
                    $options
                )));

                $apiResponse = craft()->fileCache->get($cacheKey);
            }

            if(!$apiResponse)
            {
                // call controller
                $apiResponse = craft()->analytics->apiGet($ids, $start, $end, $metrics, $options);

                if($enableCache)
                {
                    craft()->fileCache->set($cacheKey, $apiResponse, craft()->analytics->cacheExpiry());
                }
            }

            if($apiResponse)
            {
                $response['cols'] = $apiResponse['cols'];
                $response['rows'] = $apiResponse['rows'];


                // simplify cols

                foreach($response['cols'] as $k => $col)
                {
                    $colName = $col->name;

                    if(strpos($colName, 'ga:') === 0)
                    {
                        $colName = substr($colName, 3);
                    }

                    $response['cols'][$k]->name = $colName;
                }


                // simplify rows

                foreach($response['rows'] as $k => $v)
                {
                    foreach($v as $k2 => $v2)
                    {
                        if(strpos($k2, 'ga:') === 0)
                        {
                            $newKey = substr($k2, 3);

                            if($newKey != $k2)
                            {
                                $response['rows'][$k][$newKey] = $v2;
                                unset($response['rows'][$k][$k2]);
                            }
                        }
                    }
                }

                $response['success'] = true;
            }
            else
            {
                throw new Exception("Couldn't get API response");
            }
        }
        catch(\Exception $e)
        {
            $response['error'] = true;
            $response['errorMessage'] = $e->getMessage();
        }

        return $response;
    }

    public function api_deprecated($options)
    {
        $results = array(
            'cols' => array(),
            'rows' => array(),
            'success' => false,
            'error' => false
        );

        try {
            $profile = craft()->analytics->getProfile();

            // // start

            $start = date('Y-m-d', strtotime(date('Y-m-d').' -1 months'));

            if(!empty($options['start']))
            {
                $start = $options['start'];
            }

            // end

            $end = date('Y-m-d');

            if(!empty($options['end']))
            {
                $end = $options['end'];
            }

            // metrics
            if(!empty($options['metrics']))
            {
                $metrics = $options['metrics'];
            }
            else
            {
                throw new Exception("Missing option: metrics");
            }

            // params

            $params = array();

            if(!empty($options['dimensions']))
            {
                $params['dimensions'] = $options['dimensions'];
            }
            else
            {
                throw new Exception("Missing option: dimensions");
            }

            if(isset($options['params']) && is_array($options['params']))
            {
                $params = array_merge($params, $options['params']);
            }

            // request

            $r = craft()->analytics->apiGet(
                'ga:'.$profile['id'],
                $start,
                $end,
                $metrics,
                $params
            );

            if($r)
            {
                    $results['cols'] = $r['cols'];
                    $results['rows'] = $r['rows'];

                // simplify cols

                foreach($results['cols'] as $k => $col)
                {
                    $colName = $col->name;

                    if(strpos($colName, 'ga:') === 0)
                    {
                        $colName = substr($colName, 3);
                    }

                    $results['cols'][$k]->name = $colName;
                }


                // simplify rows

                foreach($results['rows'] as $k => $v)
                {
                    foreach($v as $k2 => $v2)
                    {
                        if(strpos($k2, 'ga:') === 0)
                        {
                            $newKey = substr($k2, 3);

                            if($newKey != $k2)
                            {
                                $results['rows'][$k][$newKey] = $v2;
                                unset($results['rows'][$k][$k2]);
                            }
                        }
                    }
                }

                $results['success'] = true;
            }
            else
            {
                throw new Exception("Couldn't get results");

            }

        }
        catch(\Exception $e)
        {
            $results['error'] = true;
            $results['errorMessage'] = $e->getMessage();
        }

        // return

        return $results;
    }

    public function getGeoRegionOpts($params)
    {
        $opts = array();

        if(!empty($params['world']))
        {
            $opts[]['optgroup'] = "World";
            $opts[] = array(
                "label" => "World",
                "value" => "world"
            );
        }

        if(!empty($params['continents']))
        {
            $opts[]['optgroup'] = "Continents";

            $continents = craft()->analytics->getContinents();

            foreach($continents as $continent)
            {
                $opts[] = array(
                    'label' => $continent['label'],
                    'value' => $continent['code']
                );
            }
        }

        if(!empty($params['subContinents']))
        {
            $opts[]['optgroup'] = "Sub Continents";

            $subContinents = craft()->analytics->getSubContinents();

            foreach($subContinents as $subContinent)
            {
                $opts[] = array(
                    'label' => $subContinent['label'],
                    'value' => $subContinent['code']
                );
            }
        }

        if(!empty($params['countries']))
        {
            $opts[]['optgroup'] = "Countries";

            $countries = craft()->analytics->getCountries();

            foreach($countries as $country)
            {
                if(is_array($params['countries']))
                {
                    foreach($params['countries'] as $c)
                    {
                        if($c == $country['code'])
                        {
                            $opts[] = array(
                                'label' => $country['label'],
                                'value' => $country['code']
                            );
                        }
                    }
                }
                else
                {
                    $opts[] = array(
                        'label' => $country['label'],
                        'value' => $country['code']
                    );
                }
            }
        }

        return $opts;
    }

    public function getCountries()
    {
        $json = file_get_contents(CRAFT_PLUGINS_PATH.'analytics/data/countries.json');

        $countries = json_decode($json, true);

        return $countries;
    }

    public function getCountryByCode($code)
    {
        foreach($this->getCountries() as $country)
        {
            if($country['code'] == $code)
            {
                return $country;
            }
        }
    }

    public function getCountryByLabel($label)
    {
        foreach($this->getCountries() as $country)
        {
            if($country['label'] == $label)
            {
                return $country;
            }
        }
    }

    public function getContinents()
    {
        $json = file_get_contents(CRAFT_PLUGINS_PATH.'analytics/data/continents.json');

        $continents = json_decode($json, true);

        return $continents;
    }

    public function getContinentByCode($code)
    {
        foreach($this->getContinents() as $continent)
        {
            if($continent['code'] == $code)
            {
                return $continent;
            }
        }
    }


    public function getContinentByLabel($label)
    {
        foreach($this->getContinents() as $continent)
        {
            if($continent['label'] == $label)
            {
                return $continent;
            }
        }
    }

    public function getSubContinents()
    {
        $json = file_get_contents(CRAFT_PLUGINS_PATH.'analytics/data/subContinents.json');

        $subcontinents = json_decode($json, true);

        return $subcontinents;
    }

    public function getSubContinentByCode($code)
    {
        foreach($this->getSubContinents() as $subContinent)
        {
            if($subContinent['code'] == $code)
            {
                return $subContinent;
            }
        }
    }

    public function getSubContinentByLabel($label)
    {
        foreach($this->getSubContinents() as $subContinent)
        {
            if($subContinent['label'] == $label)
            {
                return $subContinent;
            }
        }
    }

    public function secondMinute($seconds)
    {
        $minResult = floor($seconds/60);

        if($minResult < 10)
        {
            $minResult = 0 . $minResult;
        }

        $secResult = ($seconds/60 - $minResult) * 60;

        if(round($secResult) < 10){
            $secResult = 0 . round($secResult);
        }
        else
        {
            $secResult = round($secResult);
        }

        return $minResult.":".$secResult;
    }

    private function formatCell($value, $column)
    {
        switch($column['name'])
        {
            case "ga:avgTimeOnPage":
                $value = $this->secondMinute($value);
                return $value;
                break;

            case 'ga:pageviewsPerVisit':

                $value = round($value, 2);
                return $value;

                break;

            case 'ga:entranceRate':
            case 'ga:visitBounceRate':
            case 'ga:exitRate':

                $value = round($value, 2)."%";
                return $value;

                break;

            default:
                return $value;
        }
    }

    public function getRows($response)
    {
        $columns = $response['cols'];

        $newRows = array();

        if(isset($response['rows']))
        {

            $rows = $response['rows'];

            foreach($rows as $row)
            {
                $newRow = array();

                foreach($row as $k => $v)
                {
                    $newRow[$columns[$k]['name']] = $v;
                    //$this->formatCell($v, $columns[$k]);
                }

                array_push($newRows, $newRow);

            }
        }

        return $newRows;
    }

    public function cacheExpiry()
    {
        $cacheExpiry = craft()->config->get('analyticsCacheExpiry');

        if(!$cacheExpiry)
        {
            $cacheExpiry = 30 * 60; // 30 min cache
        }

        return $cacheExpiry;
    }

    public function parseApiResponse($apiResponse)
    {
        $response = array();
        $response['cols'] = $apiResponse->columnHeaders;
        $response['rows'] = $apiResponse->rows;
        $response['rows'] = $this->getRows($response);

        return $response;
    }

    public function apiGet($p1 = null, $p2 = null, $p3 = null, $p4 = null, $p5 = array())
    {
        $api = $this->getApiObject();

        if($api)
        {
            $response = $api->data_ga->get($p1, $p2, $p3, $p4, $p5);

            return $this->parseApiResponse($response);
        }
    }

    public function apiRealtimeGet($p1 = null, $p2 = null, $p3 = null, $p4 = null, $p5 = array())
    {
        $response = craft()->analytics->getApiObject()->data_realtime->get($p1, $p2, $p3, $p4, $p5);

        return $response;
        // return $this->parseApiResponse($response);
    }

    public function getChartFromData($data)
    {
        $query = $data['query'];

        $result = craft()->analytics->getApiObject()->data_ga->get(
            $query['param1'],
            $query['param2'],
            $query['param3'],
            $query['param4'],
            $query['param5']
        );

        $rows = array();

        // foreach($result['rows'] as $v)
        // {
        //     $row = array($v[0], (int) $v[1]);
        //     $rows[] = $row;
        // }

        foreach($result['rows'] as $v) {
            $itemMetric = (int) array_pop($v);
            $itemDimension = implode('.', $v);

            if($itemDimension != "(not provided)" && $itemDimension != "(not set)") {
                $item = array($itemDimension, $itemMetric);
                array_push($rows, $item);
            }
        }

        $data['rows'] = $rows;

        return $data;
    }

    public function getApiObject()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $handle = 'google';
        $namespace = 'analytics.system';


        // get token

        $token = craft()->oauth->getSystemToken($handle, $namespace);


        // provider

        $provider = craft()->oauth->getProvider($handle);

        if(!$provider) {

            Craft::log(__METHOD__.' : Could not get provider connected', LogLevel::Info, true);

            return false;
        }

        if(!$token) {

            Craft::log(__METHOD__.' : No token defined', LogLevel::Info, true);

            return false;
        }


        // init api

        $client = new Google_Client();
        $client->setApplicationName('Google+ PHP Starter Application');
        $client->setClientId($provider->clientId);
        $client->setClientSecret($provider->clientSecret);
        $client->setRedirectUri($provider->getRedirectUri());

        $api = new Google_Service_Analytics($client);

        $realToken = $token->getRealToken();
        $realToken->created = 0;
        $realToken->expires_in = $realToken->expires;
        $realToken = json_encode($realToken);

        $client->setAccessToken($realToken);

        return $api;
    }

    public function getAccount()
    {

        try {
            return @craft()->oauth->getAccount('google', 'analytics.system');
        } catch(\Exception $e) {
            return false;
        }
    }

    public function getProfile()
    {
        $r = array();

        $webProperty = $this->getWebProperty();

        $profile = craft()->fileCache->get('analytics.profile');

        if(!$profile && !empty($webProperty['accountId']))
        {
            $profiles = $this->getApiObject()->management_profiles->listManagementProfiles($webProperty['accountId'], $webProperty['id']);

            $profile = $profiles['items'][0];

            craft()->fileCache->set('analytics.profile', $profile);
        }

        if($profile)
        {
            return $profile;
        }
        else
        {
            throw new Exception("Couldn't get profile");
        }

        return $r;
    }

    public function getWebProperty()
    {

        $r = array();

        try {

            $webProperty = craft()->fileCache->get('analytics.webProperty');

            if(!$webProperty) {

                $webProperties = $this->getApiObject()->management_webproperties->listManagementWebproperties("~all");

                foreach($webProperties['items'] as $webPropertyItem) {

                    if($webPropertyItem['id'] == $this->getSetting('profileId')) {
                        $webProperty = $webPropertyItem;
                    }
                }

                craft()->fileCache->set('analytics.webProperty', $webProperty);
            }

            $r = $webProperty;

        } catch(\Exception $e) {
            $r['error'] = $e->getMessage();
        }

        return $r;
    }

    public function properties()
    {

        $properties = array("" => "Select");

        Craft::log(__METHOD__, LogLevel::Info, true);

        try {

            $api = craft()->analytics->getApiObject();

            if(!$api) {

                Craft::log(__METHOD__.' : Could not get API', LogLevel::Info, true);

                return false;
            }

            $response = $api->management_webproperties->listManagementWebproperties("~all");

            if(!$response) {
                Craft::log(__METHOD__.' : Could not list management web properties', LogLevel::Info, true);
                return false;
            }
            $items = $response['items'];


            foreach($items as $item) {
                $name = $item['id'];

                if(!empty($item['websiteUrl'])) {
                    $name .= ' - '.$item['websiteUrl'];
                } elseif(!empty($item['name'])) {
                    $name .= ' - '.$item['name'];
                }

                $properties[$item['id']] = $name;
            }

            return $properties;
        } catch(\Exception $e) {

            Craft::log(__METHOD__.' : Crashed with error : '.$e->getMessage(), LogLevel::Info, true);

            return false;
        }
    }

    public function getSetting($k)
    {
        $plugin = craft()->plugins->getPlugin('analytics');

        $settings = $plugin->getSettings();

        return $settings[$k];
    }

    public function isConfigured()
    {
        // check if plugin has finished installation process

        if(!$this->isInstalled()) {
            return false;
        }


        // check if api is available

        $api = craft()->analytics->getApiObject();

        if(!$api) {
            Craft::log(__METHOD__.' : Analytics API not available', LogLevel::Info, true);
            return false;
        }


        // check if profile id is set up

        $profileId = $this->getSetting('profileId');


        if(!$profileId) {
            Craft::log(__METHOD__.' : Analytics profileId not found', LogLevel::Info, true);
            return false;
        }

        return true;
    }

    public function isInstalled()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        // is oauth present in craft

        $oauth = craft()->plugins->getPlugin('OAuth', false);

        if(!$oauth) {
            Craft::log(__METHOD__.' : OAuth plugin files not present', LogLevel::Info, true);
            return false;
        }

        // if present, is it installed

        if(!$oauth->isInstalled) {
            Craft::log(__METHOD__.' : OAuth plugin not installed', LogLevel::Info, true);
            return false;
        }


        // try to get an account

        $token = craft()->oauth->getSystemToken('google', 'analytics.system');

        if(!$token)
        {
            Craft::log(__METHOD__." : Couldn't find a valid token", LogLevel::Info, true);

            return false;
        }

        return true;
    }


    public function getMetricOpts($params = array())
    {
        // metrics

        $json = file_get_contents(CRAFT_PLUGINS_PATH.'analytics/data/metrics.json');
        $metrics = json_decode($json);

        $newMetrics = array();

        foreach($metrics as $group => $groupMetrics)
        {
            $newMetrics[] = array('optgroup' => $group);

            foreach($groupMetrics as $metric)
            {
                $newMetrics[] = array(
                    'label' => $metric,
                    'value' => $metric,
                );
            }
        }

        $metrics = $newMetrics;


        // params

        if(count($params) > 0)
        {
            $newMetrics = array();

            foreach($metrics as $metric)
            {
                foreach($params as $param)
                {

                    if(isset($metric['value']))
                    {
                        if($metric['value'] == $param)
                        {
                            $newMetrics[] = $metric;
                        }
                    }
                }
            }

            return $newMetrics;
        }

        return $metrics;
    }

    public function getDimensionOpts($params = array())
    {
        // dimensions

        $json = file_get_contents(CRAFT_PLUGINS_PATH.'analytics/data/dimensions.json');
        $dimensions = json_decode($json);

        $newDimensions = array();

        foreach($dimensions as $group => $groupDimensions)
        {
            $newDimensions[] = array('optgroup' => $group);

            foreach($groupDimensions as $dimension)
            {
                $newDimensions[] = array(
                    'label' => $dimension,
                    'value' => $dimension,
                );
            }
        }

        $dimensions = $newDimensions;


        // params

        if(count($params) > 0)
        {
            $newDimensions = array();

            foreach($dimensions as $dimension)
            {
                foreach($params as $param)
                {
                    if(isset($dimension['value']))
                    {
                        // echo $dimension['value'].":".$param.'<br />';
                        if($dimension['value'] == $param)
                        {
                            $newDimensions[] = $dimension;
                        }
                    }
                }
            }

            return $newDimensions;
        }

        return $dimensions;
    }
}

