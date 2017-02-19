<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\etc\craft;

class AnalyticsTracking
{
    // Properties
    // =========================================================================

    /**
     * @var GATracking
     */
    private $tracking;

    // Public Methods
    // =========================================================================

    /**
     * Returns the string representation of the element.
     *
     * @return string
     */
    public function __toString()
    {
        return '';
    }

    /**
     * Constructor
     *
     * @param array|null $options
     */
    public function __construct($options = null)
    {
        if(!empty($options['accountId']))
        {
            $accountId = $options['accountId'];

            $this->tracking = new \Racecore\GATracking\GATracking($accountId);
        }
        else
        {
            throw new Exception("Account ID not provided");
        }
    }

    /**
     * Campaign
     */
    public function campaign($options)
    {
        $item = $this->tracking->createTracking('Page');
        $item = $this->_fillItem($item, $options);
        $this->tracking->sendTracking($item);
        return $this;
    }

    /**
     * Ecommerce Transaction
     */
    public function ecommerceTransaction($options)
    {
        $item = $this->tracking->createTracking('Ecommerce\Transaction');
        $item = $this->_fillItem($item, $options);
        $this->tracking->sendTracking($item);
        return $this;
    }

    /**
     * Ecommerce Item
     */
    public function ecommerceItem($options)
    {
        $item = $this->tracking->createTracking('Ecommerce\Item');
        $item = $this->_fillItem($item, $options);
        $this->tracking->sendTracking($item);
        return $this;
    }

    /**
     * Page
     */
    public function page($options)
    {
        $item = $this->tracking->createTracking('Page');
        $item = $this->_fillItem($item, $options);
        $this->tracking->sendTracking($item);
        return $this;
    }

    /**
     * Event
     */
    public function event($options)
    {
        $item = $this->tracking->createTracking('Event');
        $item = $this->_fillItem($item, $options);
        $this->tracking->sendTracking($item);
        return $this;
    }

    /**
     * Social
     */
    public function social($options)
    {
        $item = $this->tracking->createTracking('Social');
        $item = $this->_fillItem($item, $options);
        $this->tracking->sendTracking($item);
        return $this;
    }

    /**
     * App Event
     */
    public function appEvent($options)
    {
        $item = $this->tracking->createTracking('App\Event');
        $item = $this->_fillItem($item, $options);
        $this->tracking->sendTracking($item);
        return $this;
    }

    /**
     * App Screen
     */
    public function appScreen($options)
    {
        $item = $this->tracking->createTracking('App\Screen');
        $item = $this->_fillItem($item, $options);
        $this->tracking->sendTracking($item);
        return $this;
    }

    /**
     * User Timing
     */
    public function userTiming($options)
    {
        $item = $this->tracking->createTracking('User\Timing');
        $item = $this->_fillItem($item, $options);
        $this->tracking->sendTracking($item);
        return $this;
    }

    /**
     * Exception
     */
    public function exception($options)
    {
        $item = $this->tracking->createTracking('Exception');
        $item = $this->_fillItem($item, $options);
        $this->tracking->sendTracking($item);
        return $this;
    }

    /**
     * Send
     */
    public function send()
    {
        try {
            $this->tracking->send();
        }
        catch(\Exception $e)
        {
            // \dukt\analytics\Plugin::log('Couldn’t send tracking: '.$e->getMessage(), LogLevel::Error);
        }
    }

    // Private Methods
    // =========================================================================

    /**
     * Fill Item
     */
    private function _fillItem($item, $options)
    {
        if(isset($item))
        {
            $aliases = array(
                'id' => 'ID',
                'transactionId' => 'transactionID',
                'nonInteractionHit' => 'asNonInteractionHit'
            );


            foreach($options as $k => $v)
            {

                if(!empty($aliases[$k]))
                {
                    $item->{'set'.ucfirst($aliases[$k])}($v);
                }
                else
                {
                    $item->{'set'.ucfirst($k)}($v);
                }
            }

        }

        return $item;
    }
}
