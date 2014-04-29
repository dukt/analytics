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
    public function getCountries()
    {
        $json = file_get_contents(CRAFT_PLUGINS_PATH.'analytics/data/countries.json');

        $countries = json_decode($json);

        return $countries;
    }

    public function getContinents()
    {
        $json = file_get_contents(CRAFT_PLUGINS_PATH.'analytics/data/continents.json');

        $continents = json_decode($json, true);

        return $continents;
    }

    public function getSubContinents()
    {
        $json = file_get_contents(CRAFT_PLUGINS_PATH.'analytics/data/subContinents.json');

        $subcontinents = json_decode($json);

        return $subcontinents;
    }
}