<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
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
     * @var string Source ID
     */
    public $sourceId;

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
     * @var string|array|null Metrics
     */
    public $metrics;

    /**
     * @var string|array|null Dimensions
     */
    public $dimensions;

    /**
     * @var array Order Bys
     */
    public $orderBys = [];

    /**
     * @var int Offset
     */
    public $offset;

    /**
     * @var int Limit
     */
    public $limit;

    /**
     * @var array Dimension filter
     */
    public $dimensionFilter;

    /**
     * @var array Metric filter
     */
    public $metricFilter;

    /**
     * @var bool If set to false, the response does not include rows if all the retrieved metrics are equal to zero. The default is false which will exclude these rows.
     */
    public $keepEmptyRows = false;

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
        catch(\Exception $exception)
        {
            $response['error'] = true;
            $response['errorMessage'] = $exception->getMessage();
        }

        return $response;
    }
}