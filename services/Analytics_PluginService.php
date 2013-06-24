<?php

/**
 * Craft Analytics
 *
 * @package     Craft Analytics
 * @version     Version 1.0
 * @author      Benjamin David
 * @copyright   Copyright (c) 2013 - DUKT
 * @link        http://dukt.net/add-ons/craft/analytics/
 *
 */

namespace Craft;

require_once(CRAFT_PLUGINS_PATH.'analytics/vendor/autoload.php');

use VIPSoft\Unzip\Unzip;
use Symfony\Component\Filesystem\Filesystem;

class Analytics_PluginService extends BaseApplicationComponent
{
    public function update()
    {
        $zip = $this->getUpdates();
        var_dump($zip);
        // die();
        return true;
    }

    public function getUpdates()
    {
        // get update from cache

        // or refresh cache and get new updates if cache expired or forced update

        $url = 'http://dukt.net/craft/analytics/releases.xml';

        $xml = simplexml_load_file($url);


        // XML from here on

        $namespaces = $xml->getNameSpaces(true);
        $versions = array();
        $zips = array();
        if (!empty($xml->channel->item)) {
            foreach ($xml->channel->item as $version) {
                $ee_addon       = $version->children($namespaces['ee_addon']);
                $version_number = (string) $ee_addon->version;
                $versions[$version_number] = $version_number;
                $zips[$version_number] = 'http://google.fr';
                //var_dump($ee_addon);
            }
        }

        ksort($versions);

        $last_version = array_pop($versions);
        $current_version = craft()->plugins->getPlugin('Analytics')->getVersion();

        if($last_version > $current_version) {
            return $zips[$last_version];
        } else {
            return false;
        }
    }
}

