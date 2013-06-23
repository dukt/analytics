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

class Analytics_OauthService extends BaseApplicationComponent
{
    public function download()
    {
        $r = array(
                'success' => false
            );

        $filesystem = new Filesystem();
        $unzipper  = new Unzip();

        $className = 'Oauth';
        $pluginHandle = 'oauth';


        $pluginComponent = craft()->plugins->getPlugin($className, false);



        // is github or zip ?

        $pluginZipUrl = 'http://cl.ly/2F1O0w1P1Q1F/download/oauth-craft-0.9.zip';

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

            $filesystem->remove(CRAFT_PLUGINS_PATH.$pluginHandle);
            $filesystem->rename($pluginZipDir.$content[0].'/', CRAFT_PLUGINS_PATH.$pluginHandle);

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

    public function install() {
        $class = 'OAuth';

        $pluginComponent = craft()->plugins->getPlugin($class, false);

        try {
            if(!$pluginComponent->isInstalled) {
                if (craft()->plugins->installPlugin($class)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } catch(\Exception $e) {
            return false;
        }
    }

    public function enable() {
        $class = 'OAuth';

        $pluginComponent = craft()->plugins->getPlugin($class, false);

        try {
            if(!$pluginComponent->isEnabled) {
                if (craft()->plugins->enablePlugin($class)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } catch(\Exception $e) {
            return false;
        }
    }
}

