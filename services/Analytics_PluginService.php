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
        return true;
    }
}

