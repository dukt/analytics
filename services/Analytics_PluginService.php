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
    // --------------------------------------------------------------------

    public function download($pluginClass, $pluginHandle)
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $r = array('success' => false);

       $lastVersion = $this->getLastVersion($pluginClass, $pluginHandle);

        if(!$lastVersion) {
            $r['msg'] = "Couldn't get plugin last version";

            Craft::log(__METHOD__.' : Could not get last version' , LogLevel::Info, true);

            return $r;
        }

        $pluginZipUrl = $lastVersion['xml']->enclosure['url'];



        $filesystem = new Filesystem();
        $unzipper  = new Unzip();

        $pluginComponent = craft()->plugins->getPlugin($pluginClass, false);


        // plugin path

        $pluginZipDir = CRAFT_PLUGINS_PATH."_".$pluginHandle."/";
        $pluginZipPath = CRAFT_PLUGINS_PATH."_".$pluginHandle.".zip";

        try {

            // download

            $current = file_get_contents($pluginZipUrl);

            file_put_contents($pluginZipPath, $current);


            // unzip

            $content = $unzipper->extract($pluginZipPath, $pluginZipDir);


            // make a backup here ?

            // try to keep .git and .gitignore files

            if(file_exists(CRAFT_PLUGINS_PATH.$pluginHandle.'/.git') && !$pluginZipDir.$content[0].'/.git') {
                $filesystem->rename(CRAFT_PLUGINS_PATH.$pluginHandle.'/.git',
                    $pluginZipDir.$content[0].'/.git');
            }

            // if(file_exists(CRAFT_PLUGINS_PATH.$pluginHandle.'/.gitignore')) {
            //     $filesystem->copy(CRAFT_PLUGINS_PATH.$pluginHandle.'/.gitignore',
            //         $pluginZipDir.$content[0].'/.gitignore', true);
            // }

            // remove current files

            $filesystem->remove(CRAFT_PLUGINS_PATH.$pluginHandle);

            // move new files

            $filesystem->rename($pluginZipDir.$content[0].'/', CRAFT_PLUGINS_PATH.$pluginHandle);

        } catch (\Exception $e) {

            $r['msg'] = $e->getMessage();

            Craft::log(__METHOD__.' : Crashed : '.$e->getMessage() , LogLevel::Info, true);

            return $r;
        }

        try {
            // remove download files

            $filesystem->remove($pluginZipDir);
            $filesystem->remove($pluginZipPath);
        } catch(\Exception $e) {

            $r['msg'] = $e->getMessage();

            Craft::log(__METHOD__.' : Crashed : '.$e->getMessage() , LogLevel::Info, true);

            return $r;
        }

        Craft::log(__METHOD__.' : Success : '.$e->getMessage() , LogLevel::Info, true);

        $r['success'] = true;

        return $r;
    }

    // --------------------------------------------------------------------

    public function getLastVersion($pluginClass = 'Analytics', $pluginHandle = 'analytics')
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $url = 'http://dukt.net/craft/'.$pluginHandle.'/releases.xml';

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
        } else {
            Craft::log(__METHOD__.' : Could not get channel items', LogLevel::Info, true);
        }

        ksort($versions);

        $last_version = array_pop($versions);

        return $last_version;
    }

    // --------------------------------------------------------------------

    public function install($pluginClass)
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $pluginComponent = craft()->plugins->getPlugin($pluginClass, false);

        try {
            if(!$pluginComponent)
            {
                Craft::log(__METHOD__.' : '.$pluginClass.' component not found', LogLevel::Info, true);

                return false;
            }

            if(!$pluginComponent->isInstalled) {
                if (craft()->plugins->installPlugin($pluginClass)) {
                    return true;
                } else {

                    Craft::log(__METHOD__.' : '.$pluginClass.' component not installed', LogLevel::Info, true);

                    return false;
                }
            } else {
                return true;
            }
        } catch(\Exception $e) {

            Craft::log(__METHOD__.' : Crashed : '.$e->getMessage(), LogLevel::Info, true);

            return false;
        }
    }

    // --------------------------------------------------------------------

    public function enable($pluginClass)
    {
        Craft::log(__METHOD__, LogLevel::Info, true);
        $pluginComponent = craft()->plugins->getPlugin($pluginClass, false);

        try {
            if(!$pluginComponent->isEnabled) {
                if (craft()->plugins->enablePlugin($pluginClass)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } catch(\Exception $e) {
            Craft::log(__METHOD__.' : Crashed : '.$e->getMessage(), LogLevel::Info, true);
            return false;
        }
    }
}

