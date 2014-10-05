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

class Analytics_GeoService extends BaseApplicationComponent
{
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
}

