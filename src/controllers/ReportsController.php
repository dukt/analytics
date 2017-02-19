<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\controllers;

use Craft;
use craft\web\Controller;
use dukt\analytics\models\RequestCriteria;
use dukt\analytics\Plugin as Analytics;

class ReportsController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Get Real-Time Report
     *
     * @return null
     */
    public function actionRealtimeWidget()
    {
        $newVisitor = 0;
        $returningVisitor = 0;
        $total = 0;

        if(!Craft::$app->getConfig()->get('demoMode', 'analytics'))
        {
            try
            {
                $criteria = new RequestCriteria;
                $criteria->realtime = true;
                $criteria->metrics = 'ga:activeVisitors';
                $criteria->optParams = array('dimensions' => 'ga:visitorType');

                $response = Analytics::$plugin->getApi()->sendRequest($criteria);


                // total

                if(!empty($response['totalResults']))
                {
                    $total = $response['totalResults'];
                }


                // new & returning visitors

                if(!empty($response['rows']))
                {
                    $rows = $response['rows'];

                    if(!empty($rows[0][1]))
                    {
                        switch($rows[0][0])
                        {
                            case "RETURNING":
                                $returningVisitor = $rows[0][1];
                                break;

                            case "NEW":
                                $newVisitor = $rows[0][1];
                                break;
                        }
                    }

                    if(!empty($rows[1][1]))
                    {
                        switch($rows[1][0])
                        {
                            case "RETURNING":
                                $returningVisitor = $rows[1][1];
                                break;

                            case "NEW":
                                $newVisitor = $rows[1][1];
                                break;
                        }
                    }
                }
            }
            catch(\Google_Service_Exception $e)
            {
                $errors = $e->getErrors();
                $errorMsg = $e->getMessage();

                if(isset($errors[0]['message']))
                {
                    $errorMsg = $errors[0]['message'];
                }

                // \dukt\analytics\Plugin::log('Couldn’t get realtime widget data: '.$errorMsg."\r\n".print_r($errors, true), LogLevel::Error);

                return $this->asErrorJson($errorMsg);
            }
            catch(\Exception $e)
            {
                $errorMsg = $e->getMessage();
                // \dukt\analytics\Plugin::log('Couldn’t get element data: '.$errorMsg, LogLevel::Error);
                return $this->asErrorJson($errorMsg);
            }
        }
        else
        {
            // Demo Mode
            $newVisitor = 5;
            $returningVisitor = 7;
            $total = ($newVisitor + $returningVisitor);
        }

        return $this->asJson(array(
            'total' => $total,
            'newVisitor' => $newVisitor,
            'returningVisitor' => $returningVisitor
        ));
    }

    /**
     * Get report
     *
     * @return null
     */
    public function actionReportWidget()
    {
        try
        {
            $profileId = Analytics::$plugin->getAnalytics()->getProfileId();

            $request = [
                'chart' => Craft::$app->getRequest()->getBodyParam('chart'),
                'period' => Craft::$app->getRequest()->getBodyParam('period'),
                'options' => Craft::$app->getRequest()->getBodyParam('options'),
            ];

            $cacheId = ['getReport', $request, $profileId];

            $response = Analytics::$plugin->cache->get($cacheId);

            if(!$response)
            {
                $response = Analytics::$plugin->reports->getReport($request);

                if($response)
                {
                    Analytics::$plugin->cache->set($cacheId, $response);
                }
            }

            return $this->asJson($response);
        }
        catch(\Google_Service_Exception $e)
        {
            $errors = $e->getErrors();
            $errorMsg = $e->getMessage();

            if(isset($errors[0]['message']))
            {
                $errorMsg = $errors[0]['message'];
            }

            // \dukt\analytics\Plugin::log('Couldn’t get report widget data: '.$errorMsg."\r\n".print_r($errors, true), LogLevel::Error);

            return $this->asErrorJson($errorMsg);
        }
        catch(\Exception $e)
        {
            $errorMsg = $e->getMessage();
            // \dukt\analytics\Plugin::log('Couldn’t get element data: '.$errorMsg, LogLevel::Error);
            return $this->asErrorJson($errorMsg);
        }
    }

    /**
     * Get Element Report
     *
     * @param array $variables
     *
     * @return null
     */
    public function actionElement(array $variables = array())
    {
        try
        {
            $elementId = Craft::$app->getRequest()->getRequiredParam('elementId');
            $locale = Craft::$app->getRequest()->getRequiredParam('locale');
            $metric = Craft::$app->getRequest()->getRequiredParam('metric');

            $uri = Analytics::$plugin->getAnalytics()->getElementUrlPath($elementId, $locale);

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

                $criteria = new RequestCriteria;
                $criteria->startDate = $start;
                $criteria->endDate = $end;
                $criteria->metrics = $metric;
                $criteria->optParams = $optParams;

                $cacheId = ['ReportsController.actionGetElementReport', $criteria->getAttributes()];
                $response = Analytics::$plugin->cache->get($cacheId);

                if(!$response)
                {
                    $response = Analytics::$plugin->getApi()->sendRequest($criteria);

                    if($response)
                    {
                        Analytics::$plugin->cache->set($cacheId, $response);
                    }
                }

                return $this->asJson([
                    'type' => 'area',
                    'chart' => $response
                ]);
            }
            else
            {
               throw new Exception("Element doesn't support URLs.", 1);
            }
        }
        catch(\Google_Service_Exception $e)
        {
            $errors = $e->getErrors();
            $errorMsg = $e->getMessage();

            if(isset($errors[0]['message']))
            {
                $errorMsg = $errors[0]['message'];
            }

            // \dukt\analytics\Plugin::log('Couldn’t get element data: '.$errorMsg."\r\n".print_r($errors, true), LogLevel::Error);

            return $this->asErrorJson($errorMsg);
        }
        catch(\Exception $e)
        {
            $errorMsg = $e->getMessage();
            // \dukt\analytics\Plugin::log('Couldn’t get element data: '.$errorMsg, LogLevel::Error);
            return $this->asErrorJson($errorMsg);
        }
    }
}
