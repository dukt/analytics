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
 * Class ReportRequestCriteria
 *
 * @package dukt\analytics\models
 */
class ReportRequestCriteria extends Model
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
     * @var int Page size
     */
    public $pageSize;

    /**
     * @var string Filters expression
     */
    public $filtersExpression;

    // Public Methods
    // =========================================================================

    /**
     * Sends the request
     *
     * @return array
     */
    public function send(bool $toArray = true)
    {
        $response = array(
            'success' => false,
            'error' => false
        );

        try
        {
            $response['data'] = Analytics::$plugin->getApis()->getAnalyticsReporting()->getReport($this, $toArray);
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