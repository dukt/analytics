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

class AnalyticsController extends BaseController
{
    private $handle = 'google';

    private $scopes = array(
        'userinfo_profile',
        'userinfo_email',
        'analytics'
    );

    private $params = array(
        'access_type' => 'offline',
        'approval_prompt' => 'force'
    );

    /**
     * Settings
     */
    public function actionSettings()
    {
        $plugin = craft()->plugins->getPlugin('analytics');
        $settings = $plugin->getSettings();
        $propertiesOpts = craft()->analytics->getPropertiesOpts();

        $this->renderTemplate('analytics/settings', array(
            'settings' => $settings,
            'propertiesOpts' => $propertiesOpts
        ));
    }

    /**
     * Connect
     */
    public function actionConnect()
    {
        if($response = craft()->oauth->connect(array(
            'plugin'   => 'analytics',
            'provider' => $this->handle,
            'scopes'   => $this->scopes,
            'params'   => $this->params
        )))
        {
            if($response['success'])
            {
                // token
                $token = $response['token'];

                // save token
                craft()->analytics->saveToken($token);

                // session notice
                craft()->userSession->setNotice(Craft::t("Connected to Google Analytics."));
            }
            else
            {
                // session notice
                craft()->userSession->setError(Craft::t($response['errorMsg']));
            }

            $this->redirect($response['redirect']);
        }
    }

    /**
     * Disconnect
     */
    public function actionDisconnect()
    {
        // reset token
        craft()->analytics->saveToken(null);

        // set notice
        craft()->userSession->setNotice(Craft::t("Disconnected from Google Analytics."));

        // redirect
        $redirect = craft()->request->getUrlReferrer();
        $this->redirect($redirect);
    }

    /**
     * Element Report
     */
    public function actionElementReport(array $variables = array())
    {
        try {
            $elementId = craft()->request->getRequiredParam('elementId');
            $locale = craft()->request->getRequiredParam('locale');
            $metric = craft()->request->getRequiredParam('metric');

            $uri = craft()->analytics->getElementUrlPath($elementId, $locale);

            if($uri)
            {
                if($uri == '__home__')
                {
                    $uri = '';
                }

                $profile = craft()->analytics->getProfile();
                $start = date('Y-m-d', strtotime('-1 month'));
                $end = date('Y-m-d');
                $end = date('Y-m-d');
                $metrics = $metric;
                $dimensions = 'ga:date';

                $options = array(
                        'dimensions' => $dimensions,
                        'filters' => "ga:pagePath==".$uri
                    );

                $data = array(
                    $profile['id'],
                    $start,
                    $end,
                    $metrics,
                    $options
                );

                $enableCache = true;

                if(craft()->config->get('disableCache', 'analytics') == true)
                {
                    $enableCache = false;
                }

                if($enableCache)
                {
                    $cacheKey = 'analytics/elementReport/'.md5(serialize($data));

                    $response = craft()->fileCache->get($cacheKey);

                    if(!$response)
                    {
                        $response = craft()->analytics->apiGet(
                            'ga:'.$profile['id'],
                            $start,
                            $end,
                            $metrics,
                            $options
                        );

                        craft()->fileCache->set($cacheKey, $response, craft()->analytics->cacheExpiry());
                    }
                }
                else
                {
                    $response = craft()->analytics->apiGet(
                        'ga:'.$profile['id'],
                        $start,
                        $end,
                        $metrics,
                        $options
                    );

                    //var_dump($options);
                }

                $this->returnJson(array('apiResponse' => $response));
            }
            else
            {
               throw new Exception("Element doesn't support URLs.", 1);
            }
        }
        catch(\Exception $e)
        {
            $this->returnErrorJson($e->getMessage());
        }
    }

    private function catchError($e)
    {
        $errors = $e->getErrors();

        if(is_array($errors))
        {
            $error = $errors[0];
        }
        else
        {
            $error = $e->getMessage();
        }

        return $error;
    }
}