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
            $dataSource = craft()->analytics->getDataSource($dataSourceClassName);
            $postData = craft()->request->getPost();
            $response = $dataSource->getChartData($postData);

            $this->returnJson($response);
        }
        catch(\Exception $e)
        {
            $this->returnErrorJson($e->getMessage());
        }
    }
}
