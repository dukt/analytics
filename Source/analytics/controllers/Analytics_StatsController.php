<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_StatsController extends BaseController
{
    // Public Methods
    // =========================================================================

    public function actionGetChart()
    {
        $chart = craft()->request->getRequiredParam('chart');

        try
        {
            $dataSourceClassName = 'GoogleAnalytics';
            $request = craft()->request->getPost();

            $requestHash = $request;
            unset($requestHash['CRAFT_CSRF_TOKEN']);
            $requestHash = md5(serialize($requestHash));
            
            $cacheKey = 'analytics.dataSources.'.$dataSourceClassName.'.getChartData.'.$requestHash;

            $response = craft()->cache->get($cacheKey);

            if(!$response)
            {
                $dataSource = craft()->analytics->getDataSource($dataSourceClassName);
                $response = $dataSource->getChartData($request);
                craft()->cache->set($cacheKey, $response);
            }

            $this->returnJson($response);
        }
        catch(\Exception $e)
        {
            $this->returnErrorJson($e->getMessage());
        }
    }
}
