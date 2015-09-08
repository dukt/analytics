<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

use Guzzle\Http\Client;

class Analytics_PluginService extends BaseApplicationComponent
{
    // Public Methods
    // =========================================================================

    public function requireDependencies()
    {
        $plugin = craft()->plugins->getPlugin('analytics');
        $pluginDependencies = $plugin->getPluginDependencies();

        if (count($pluginDependencies) > 0)
        {
            $url = UrlHelper::getUrl('analytics/_special/dependencies');
            craft()->request->redirect($url);
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * Download
     */
    public function download($pluginHandle)
    {
        // -------------------------------
        // Get ready to download & unzip
        // -------------------------------

        $return = array('success' => false);

        $pluginComponent = craft()->plugins->getPlugin($pluginHandle, false);


        // plugin path

        $pluginZipDir = CRAFT_PLUGINS_PATH."_".$pluginHandle."/";
        $pluginZipPath = CRAFT_PLUGINS_PATH."_".$pluginHandle.".zip";


        // remote plugin zip url

        $remotePlugin = $this->_getRemotePlugin($pluginHandle);

        if(!$remotePlugin)
        {
            $return['msg'] = "Couldn’t get plugin’s last version";

            Craft::log($return['msg'], LogLevel::Error);

            return $return;
        }

        $remotePluginZipUrl = $remotePlugin['xml']->enclosure['url'];

        // -------------------------------
        // Download & Install
        // -------------------------------

        try {

            // download remotePluginZipUrl to pluginZipPath

            $client = new \Guzzle\Http\Client();
            $request = $client->get($remotePluginZipUrl);
            $response = $request->send();
            $body = $response->getBody();

            // Make sure we're at the beginning of the stream.
            $body->rewind();

            // Write it out to the file
            IOHelper::writeToFile($pluginZipPath, $body->getStream(), true);

            // Close the stream.
            $body->close();

            // unzip pluginZipPath into pluginZipDir

            Zip::unzip($pluginZipPath, $pluginZipDir);
            $contents = IOHelper::getFiles($pluginZipDir);

            $pluginUnzipDir = false;

            foreach($contents as $content)
            {
                if(strrpos($content, "__MACOSX") !== 0)
                {
                    $pluginUnzipDir = $content;
                }
            }

            // move files we want to keep from their current location to unzipped location
            // keep : .git

            if(file_exists(CRAFT_PLUGINS_PATH.$pluginHandle.'/.git') && !$pluginUnzipDir.'/.git') {
                IOHelper::rename(CRAFT_PLUGINS_PATH.$pluginHandle.'/.git',
                    $pluginUnzipDir.'/.git');
            }

            //rename($path, $newName, $suppressErrors = false)


            // remove current files
            // make a backup of existing plugin (to storage ?) ?
            //deleteFolder($path, $suppressErrors = false)
            IOHelper::deleteFolder(CRAFT_PLUGINS_PATH.$pluginHandle);


            // move new files to final destination

            IOHelper::rename($pluginUnzipDir.'/'.$pluginHandle.'/', CRAFT_PLUGINS_PATH.$pluginHandle);

            // delete zip
            IOHelper::deleteFile($pluginZipPath);

        } catch (\Exception $e) {

            $return['msg'] = $e->getMessage();

            Craft::log('Couldn’t download plugin: '.$e->getMessage() , LogLevel::Error);

            return $return;
        }


        // remove download files

        try {
            IOHelper::deleteFolder($pluginZipDir);
            IOHelper::deleteFolder($pluginZipPath);
        } catch(\Exception $e) {

            $return['msg'] = $e->getMessage();

            Craft::log('Couldn’t remove download files: '.$e->getMessage() , LogLevel::Error);

            return $return;
        }

        $return['success'] = true;

        return $return;
    }

    /**
     * Enable
     */
    public function enable($pluginHandle)
    {
        $pluginComponent = craft()->plugins->getPlugin($pluginHandle, false);

        try {

            if(!$pluginComponent->isEnabled) {
                if (craft()->plugins->enablePlugin($pluginHandle)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }

        } catch(\Exception $e) {

            Craft::log('Couldn’t enable plugin: '.$e->getMessage(), LogLevel::Error);

            return false;
        }
    }

    /**
     * Install
     */
    public function install($pluginHandle)
    {
        craft()->plugins->loadPlugins();

        $pluginComponent = craft()->plugins->getPlugin($pluginHandle, false);

        try {
            if(!$pluginComponent)
            {
                Craft::log($pluginHandle.' plugin not found', LogLevel::Error);

                return false;
            }

            if(!$pluginComponent->isInstalled) {
                if (craft()->plugins->installPlugin($pluginHandle)) {
                    return true;
                } else {

                    Craft::log($pluginHandle.' plugin not installed', LogLevel::Error);

                    return false;
                }
            } else {
                return true;
            }
        } catch(\Exception $e) {

            Craft::log('Couldn’t install plugin: '.$e->getMessage(), LogLevel::Error);

            return false;
        }
    }

    // Private Methods
    // =========================================================================

    /**
     * Get Remote Plugin
     */
    private function _getRemotePlugin($pluginHandle)
    {
        $url = 'https://dukt.net/craft/'.$pluginHandle.'/releases.xml';

        $client = new Client();
        $response = $client->get($url)->send();

        $xml = $response->xml();

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
                return $versions[$version_number];
            }
        } else {
            Craft::log('Could not get channel items', LogLevel::Error);
        }
    }
}

