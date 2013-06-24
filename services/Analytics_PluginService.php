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
    private $pluginClass = 'Analytics';
    private $pluginHandle = 'analytics';

    public function update()
    {
        $lastVersion = $this->getLastVersion();

        $pluginZipUrl = $lastVersion['xml']->enclosure['url'];

        $r = array('success' => false);

        $filesystem = new Filesystem();
        $unzipper  = new Unzip();

        $pluginComponent = craft()->plugins->getPlugin($this->pluginClass, false);


        // plugin path

        $pluginZipDir = CRAFT_PLUGINS_PATH."_".$this->pluginHandle."/";
        $pluginZipPath = CRAFT_PLUGINS_PATH."_".$this->pluginHandle.".zip";

        try {

            // download

            $current = file_get_contents($pluginZipUrl);

            file_put_contents($pluginZipPath, $current);


            // unzip

            $content = $unzipper->extract($pluginZipPath, $pluginZipDir);


            // make a backup here ?

            // try to keep .git and .gitignore files

            @$filesystem->rename(CRAFT_PLUGINS_PATH.$this->pluginHandle.'/.git',
                $pluginZipDir.$content[0].'/.git');
            @$filesystem->rename(CRAFT_PLUGINS_PATH.$this->pluginHandle.'/.gitignore',
                $pluginZipDir.$content[0].'/.gitignore');

            // remove current files

            $filesystem->remove(CRAFT_PLUGINS_PATH.$this->pluginHandle);

            // move new files

            $filesystem->rename($pluginZipDir.$content[0].'/', CRAFT_PLUGINS_PATH.$this->pluginHandle);

        } catch (\Exception $e) {
            $r['msg'] = $e->getMessage();
            return $r;
        }

        try {
            // remove download files

            $filesystem->remove($pluginZipDir);
            $filesystem->remove($pluginZipPath);
        } catch(\Exception $e) {
            $r['msg'] = $e->getMessage();

            return $r;
        }

        $r['success'] = true;

        return $r;
    }

    public getLastVersion($url = 'http://dukt.net/craft/analytics/releases.xml';)
    {
        // or refresh cache and get new updates if cache expired or forced update

        $xml = simplexml_load_file($url);


        // XML from here on

        $namespaces = $xml->getNameSpaces(true);

        $versions = array();
        $zips = array();
        $xml_version = array();

        if (!empty($xml->channel->item)) {
            foreach ($xml->channel->item as $version) {
                $ee_addon       = $version->children($namespaces['ee_addon']);
                $version_number = (string) $ee_addon->version;
                $versions[$version_number] = array('xml' => $version, 'addon' => $ee_addon);
            }
        }

        ksort($versions);

        $last_version = array_pop($versions);

        $current_version = craft()->plugins->getPlugin('Analytics')->getVersion();

        if($last_version['addon']->version > $current_version) {

            // there is an update available

            return (string) $xml_versions[$last_version];
        } else {
            return false;
        }
    }
}

