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
    public function actionCustomReport(array $variables = array())
    {
        // profile
        $profile = craft()->analytics->getProfile();

        // start / end dates
        $start = craft()->request->getParam('start');
        $end = craft()->request->getParam('end');

        if(empty($start))
        {
            $start = date('Y-m-d', strtotime('-1 month'));
        }

        if(empty($end))
        {
            $end = date('Y-m-d');
        }

        // widget

        $id = craft()->request->getParam('id');

        $widget = craft()->dashboard->getUserWidgetById($id);

        $metric = $widget->settings['options']['metric'];

        $dimensions = array('dimensions' => $widget->settings['options']['dimension']);

        $response = craft()->analytics->api()->data_ga->get(
            'ga:'.$profile['id'],
            $start,
            $end,
            $metric,
            $dimensions
        );

        $this->returnJson(array(
            'widget' => $widget,
            'apiResponse' => $response
        ));
    }
}