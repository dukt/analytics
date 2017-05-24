<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\models;

use craft\base\Model;
use dukt\analytics\Plugin as Analytics;

/**
 * Class ReportingRequestCriteria
 *
 * @package dukt\analytics\models
 */
class ReportingRequestCriteria extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var string View ID
     */
    public $viewId;

    /**
     * @var string Start date
     */
    public $startDate;

    /**
     * @var string End date
     */
    public $endDate;

    /**
     * @var string Metrics
     */
    public $metrics;

    /**
     * @var array Dimensions
     */
    public $dimensions;

    /**
     * @var array Order Bys
     */
    public $orderBys;

    /**
     * @var int Order Bys
     */
    public $pageSize;

    // Public Methods
    // =========================================================================

    /**
     * Sends the request
     *
     * @return array
     */
    public function send()
    {
        $response = array(
            'success' => false,
            'error' => false
        );

        try
        {
            $response['data'] = Analytics::$plugin->getAnalyticsReportingApi()->sendRequest($this);
            $response['success'] = true;
        }
        catch(\Exception $e)
        {
            $response['error'] = true;
            $response['errorMessage'] = $e->getMessage();
        }

        return $response;
    }
}