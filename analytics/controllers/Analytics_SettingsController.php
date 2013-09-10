<?php

/**
 * Craft Analytics by Dukt
 *
 * @package   Craft Analytics
 * @author    Benjamin David
 * @copyright Copyright (c) 2013, Dukt
 * @license   http://dukt.net/craft/analytics/docs#license
 * @link      http://dukt.net/craft/analytics/
 */

namespace Craft;

class Analytics_SettingsController extends BaseController
{
    public function actionSave()
    {
        Craft::log(__METHOD__, LogLevel::Info, true);

        $settings = Analytics_SettingsRecord::model()->find();

        if(!$settings) {
            $settings = new Analytics_SettingsRecord();
        }

        $settings->options = array('profileId' => craft()->request->getPost('profileId'));

        if($settings->save()) {
            Craft::log(__METHOD__." : Plugins settings saved", LogLevel::Info, true);
            craft()->userSession->setNotice(Craft::t('Plugin settings saved.'));
        } else {
            Craft::log(__METHOD__." : Plugins settings could not be saved", LogLevel::Info, true);
            craft()->userSession->setError(Craft::t('Plugin settings couldn\'t be saved.'));
        }

        $this->redirect('analytics');
    }
}