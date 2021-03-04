<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2021, Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
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
     * @var string Google Analytics View ID
     */
    public $gaViewId;

    /**
     * @var string Start date
     */
    public $startDate;

    /**
     * @var string End date
     */
    public $endDate;

    /**
     * @var string Sampling level
     */
    public $samplingLevel;

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
     * @var string A continuation token to get the next page of the results. Adding this to the request will return the rows after the pageToken.
     */
    public $pageToken;

    /**
     * @var string Filters expression
     */
    public $filtersExpression;

    /**
     * @var bool If set to false, the response does not include rows if all the retrieved metrics are equal to zero. The default is false which will exclude these rows.
     */
    public $includeEmptyRows;

    /**
     * @var bool If set to true, hides the total of all metrics for all the matching rows, for every date range. The default false and will return the totals.
     */
    public $hideTotals;

    /**
     * @var bool If set to true, hides the minimum and maximum across all matching rows. The default is false and the value ranges are returned.
     */
    public $hideValueRanges;

    // Public Methods
    // =========================================================================

    /**
     * Sends the request
     *
     * @param bool $toArray
     *
     * @return array
     */
    public function send(bool $toArray = false)
    {
        $response = array(
            'success' => false,
            'error' => false
        );

        try
        {
            $response['report'] = Analytics::$plugin->getApis()->getAnalyticsReporting()->getReport($this, $toArray);
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