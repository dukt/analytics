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
            $response['data'] = Analytics::$plugin->api->sendRequest($this);

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

    // Protected Methods
    // =========================================================================

    /**
     * @inheritDoc BaseModel::defineAttributes()
     *
     * @return array
     */
    protected function defineAttributes()
    {
        return array(
            'ids' => AttributeType::String,
            'startDate' => AttributeType::String,
            'endDate' => AttributeType::String,
            'metrics' => AttributeType::String,
            'optParams' => array(AttributeType::Mixed, 'default' => array()),
            'format' => AttributeType::String,
            'realtime' => array(AttributeType::Bool, 'default' => false),
            'enableCache' => array(AttributeType::Bool, 'default' => true),
        );
    }
}