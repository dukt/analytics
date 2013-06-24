<?php

/**
 * Craft Directory by Dukt
 *
 * @package   Craft Directory
 * @author    Benjamin David
 * @copyright Copyright (c) 2013, Dukt
 * @license   http://docs.dukt.net/craft/directory/license
 * @link      http://dukt.net/craft/analytics/license
 */

namespace Craft;

class Analytics_PluginController extends BaseController
{
    // --------------------------------------------------------------------

    private $pluginHandle = 'analytics';

    // --------------------------------------------------------------------

    public function actionUpdate()
    {
        // $updates = craft()->analytics->getUpdates();

        // var_dump($updates);
        // die();

        $update = craft()->analytics_plugin->update();

        if($update['success'] == true) {
            craft()->userSession->setNotice(Craft::t('Analytics plugin updated.'));
        } else {
            craft()->userSession->setError(Craft::t('Couldn’t update Analytics plugin.'));
        }


        $this->redirect('analytics/settings');
    }
}