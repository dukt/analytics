<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_ReportsController extends BaseController
{
    // Public Methods
    // =========================================================================

    /**
     * Real-Time Visitors
     *
     * @return null
     */
    public function actionGetRealtimeReport()
    {
        $newVisitor = 0;
        $returningVisitor = 0;
        $total = 0;

        if(!craft()->config->get('demoMode', 'analytics'))
        {
            try
            {
                $criteria = new Analytics_RequestCriteriaModel;
                $criteria->realtime = true;
                $criteria->metrics = 'ga:activeVisitors';
                $criteria->optParams = array('dimensions' => 'ga:visitorType');

                $response = craft()->analytics->sendRequest($criteria);


                // total

                if(!empty($response['totalResults']))
                {
                    $total = $response['totalResults'];
                }


                // new & returning visitors

                if(!empty($response['rows']))
                {
                    $rows = $response['rows'];

                    if(!empty($rows[0][1]['v']))
                    {
                        switch($rows[0][0]['v'])
                        {
                            case "RETURNING":
                            $returningVisitor = $rows[0][1]['v'];
                            break;

                            case "NEW":
                            $newVisitor = $rows[0][1]['v'];
                            break;
                        }
                    }

                    if(!empty($rows[1][1]['v']))
                    {
                        switch($rows[1][0]['v'])
                        {
                            case "RETURNING":
                            $returningVisitor = $rows[1][1]['v'];
                            break;

                            case "NEW":
                            $newVisitor = $rows[1][1]['v'];
                            break;
                        }
                    }
                }
            }
            catch(\Exception $e)
            {
                $this->returnErrorJson($e->getMessage());
            }
        }
        else
        {
            $newVisitor = 5;
            $returningVisitor = 7;
            $total = ($newVisitor + $returningVisitor);
        }

        $this->returnJson(array(
            'total' => $total,
            'newVisitor' => $newVisitor,
            'returningVisitor' => $returningVisitor
        ));
    }

    public function actionGetChartReport()
    {
        $chart = craft()->request->getRequiredParam('chart');

        try
        {
            $profileId = craft()->analytics->getProfileId();
            $request = craft()->request->getPost();

            $cacheId = ['getChartData', $request, $profileId];
            $response = craft()->analytics_cache->get($cacheId);

            if(!$response)
            {
                $response = craft()->analytics_reports->getChartReport($request);

                if($response)
                {
                    craft()->analytics_cache->set($cacheId, $response);
                }
            }

            $this->returnJson($response);
        }
        catch(\Exception $e)
        {
            if(method_exists($e, 'getErrors'))
            {
                $errors = $e->getErrors();

                if(isset($errors[0]['message']))
                {
                    $this->returnErrorJson(Craft::t($errors[0]['message']));
                }
            }

            $this->returnErrorJson($e->getMessage());
        }
    }

    /**
     * Element Report
     *
     * @param array $variables
     *
     * @return null
     */
    public function actionGetElementReport(array $variables = array())
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

                $start = date('Y-m-d', strtotime('-1 month'));
                $end = date('Y-m-d');
                $dimensions = 'ga:date';

                $optParams = array(
                        'dimensions' => $dimensions,
                        'filters' => "ga:pagePath==".$uri
                    );

                $criteria = new Analytics_RequestCriteriaModel;
                $criteria->startDate = $start;
                $criteria->endDate = $end;
                $criteria->metrics = $metric;
                $criteria->optParams = $optParams;

                $cacheId = ['ReportsController.actionGetElementReport', $criteria->getAttributes()];
                $response = craft()->analytics_cache->get($cacheId);

                if(!$response)
                {
                    $response = craft()->analytics->sendRequest($criteria);

                    if($response)
                    {
                        craft()->analytics_cache->set($cacheId, $response);
                    }
                }

                $this->returnJson([
                    'type' => 'area',
                    'chart' => $response
                ]);
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
}
