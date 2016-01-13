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
        if(!craft()->config->get('demoMode', 'analytics'))
        {
            try
            {
                $data = array(
                    'newVisitor' => 0,
                    'returningVisitor' => 0
                );

                $criteria = new Analytics_RequestCriteriaModel;
                $criteria->realtime = true;
                $criteria->metrics = 'ga:activeVisitors';
                $criteria->optParams = array('dimensions' => 'ga:visitorType');

                $results = craft()->analytics->sendRequest($criteria);

                if(!empty($results['totalResults']))
                {
                    $data['total'] = $results['totalResults'];
                }

                if(!empty($results['rows'][0][1]['v']))
                {
                    switch($results['rows'][0][0]['v'])
                    {
                        case "RETURNING":
                        $data['returningVisitor'] = $results['rows'][0][1]['v'];
                        break;

                        case "NEW":
                        $data['newVisitor'] = $results['rows'][0][1]['v'];
                        break;
                    }
                }

                if(!empty($results['rows'][1][1]['v']))
                {
                    switch($results['rows'][1][0]['v'])
                    {
                        case "RETURNING":
                        $data['returningVisitor'] = $results['rows'][1][1]['v'];
                        break;

                        case "NEW":
                        $data['newVisitor'] = $results['rows'][1][1]['v'];
                        break;
                    }
                }

                $this->returnJson($data);
            }
            catch(\Exception $e)
            {
                $this->returnErrorJson($e->getMessage());
            }
        }
        else
        {
            $data = [
                'newVisitor' => 5,
                'returningVisitor' => 7
            ];

            $this->returnJson($data);
        }
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
                $dataSource = craft()->analytics->getDataSource();

                $response = $dataSource->getChartData($request);

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
