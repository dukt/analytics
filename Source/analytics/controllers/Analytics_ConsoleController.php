<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_ConsoleController extends BaseController
{
    // Public Methods
    // =========================================================================

    /**
     * Console
     *
     * @param array $variables
     *
     * @return null
     */
    public function actionIndex(array $variables = array())
    {
        if(empty($variables['profileId']))
        {
            $profile = craft()->analytics->getProfile();
            $variables['profileId'] = $profile['id'];
        }

        $this->renderTemplate('analytics/console', $variables);
    }

    /**
     * Console Send
     *
     * @return null
     */
    public function actionSend()
    {
        // params
        $profileId = craft()->request->getParam('profileId');
        $start = craft()->request->getParam('start');
        $end = craft()->request->getParam('end');
        $metrics = craft()->request->getParam('metrics');
        $optParams = craft()->request->getParam('optParams');

        // send request
        $criteria = new Analytics_RequestCriteriaModel;
        $criteria->ids = $profileId;
        $criteria->startDate = $start;
        $criteria->endDate = $end;
        $criteria->metrics = $metrics;
        $criteria->optParams = $optParams;

        $response = craft()->analytics->sendRequest($criteria);

        // set route variables
        craft()->urlManager->setRouteVariables(array(
            'profileId' => $profileId,
            'start' => $start,
            'end' => $end,
            'metrics' => $metrics,
            'optParams' => $optParams,
            'response' => $response
        ));
    }
}