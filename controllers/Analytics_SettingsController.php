<?php

/**
 * Craft Directory by Dukt
 *
 * @package   Craft Directory
 * @author    Benjamin David
 * @copyright Copyright (c) 2013, Dukt
 * @license   http://docs.dukt.net/craft/directory/license
 * @link      http://dukt.net/craft/directory
 */

namespace Craft;

class Analytics_SettingsController extends BaseController
{
    public function actionSave()
    {
        $settings = Analytics_SettingsRecord::model()->find();

        if(!$settings) {
            $settings = new Analytics_SettingsRecord();
        }

        $settings->options = array('profileId' => craft()->request->getPost('profileId'));

        if($settings->save()) {
            craft()->userSession->setNotice(Craft::t('Plugin settings saved.'));
        } else {
            craft()->userSession->setError(Craft::t('Plugin settings couldn\'t be saved.'));
        }

        $this->redirect('analytics');
    }
}