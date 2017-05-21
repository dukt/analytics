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
 * Class RequestCriteria
 *
 * @package dukt\analytics\models
 */
class RequestCriteria extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var string IDs
     */
    public $ids;

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
     * @var array Option parameters
     */
    public $optParams = [];

    /**
     * @var string Format
     */
    public $format;

    /**
     * @var bool Realtime
     */
    public $realtime = false;

    /**
     * @var bool Enable cache
     */
    public $enableCache = true;

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
            $response['data'] = Analytics::$plugin->getAnalyticsApi()->sendRequest($this);

            if(!isset($options['format']) || (isset($options['format']) && $options['format'] != 'gaData'))
            {

                $response['cols'] = $response['data']['cols'];
                $response['rows'] = $response['data']['rows'];
            }

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