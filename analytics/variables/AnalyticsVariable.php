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

class AnalyticsVariable
{
    public function getMetricOpts($params = array())
    {
        return craft()->analytics->getMetricOpts($params);
    }

    public function getDimensionOpts($params = array())
    {
        return craft()->analytics->getDimensionOpts($params);
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

            $continents = craft()->analytics_geo->getContinents();

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

            $subContinents = craft()->analytics_geo->getSubContinents();

            foreach($subContinents as $subContinent)
            {
                $opts[] = array(
                    'label' => $subContinent->label,
                    'value' => $subContinent->code
                );
            }
        }

        if(!empty($params['countries']))
        {
            $opts[]['optgroup'] = "Countries";

            $countries = craft()->analytics_geo->getCountries();

            foreach($countries as $country)
            {
                if(is_array($params['countries']))
                {
                    foreach($params['countries'] as $c)
                    {
                        if($c == $country->code)
                        {
                            $opts[] = array(
                                'label' => $country->label,
                                'value' => $country->code
                            );
                        }
                    }
                }
                else
                {
                    $opts[] = array(
                        'label' => $country->label,
                        'value' => $country->code
                    );
                }
            }
        }

        return $opts;
    }

    public function getProfile()
    {
        return craft()->analytics->getProfile();
    }

    public function getWebProperty()
    {
        return craft()->analytics->getWebProperty();
    }

    public function isConfigured()
    {
        return craft()->analytics->isConfigured();
    }

    public function properties()
    {
        return craft()->analytics->properties();
    }
    public function getAccount()
    {
        return craft()->analytics->getAccount();
    }

    public function api()
    {
        return craft()->analytics->api();
    }
}