<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\models;

use craft\base\Model;
use dukt\analytics\Plugin as Analytics;

class RequestCriteria extends Model
{
    // Properties
    // =========================================================================

    public $ids;
    public $startDate;
    public $endDate;
    public $metrics;
    public $optParams;
    public $format;
    public $realtime;
    public $enableCache;

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
            $response['data'] = Analytics::$plugin->getApi()->sendRequest($this);

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