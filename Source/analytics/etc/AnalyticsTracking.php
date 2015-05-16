<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

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
        $this->tracking = new \Racecore\GATracking\GATracking();


        // accountId

        $accountId = false;

        if(!empty($options['accountId']))
        {
            $accountId = $options['accountId'];
            unset($options['accountId']);
        }
        else
        {
            $webProperty = craft()->analytics->getWebProperty();

            if ($webProperty)
            {
                $accountId = $webProperty->id;
            }
        }

        $this->tracking->setAccountID($accountId);


        // clientId

        if(!empty($options['clientId']))
        {
            $clientId = $options['clientId'];
            $this->tracking->setClientID($clientId);
            unset($options['clientId']);
        }


        // userId

        if(!empty($options['userId']))
        {
            $userId = $options['userId'];
            $this->tracking->setUserID($userId);
            unset($options['userId']);
        }
    }

    /**
     * Campaign
     */
    public function campaign($options)
    {
        $item = new \Racecore\GATracking\Tracking\Page();
        $item = $this->_fillItem($item, $options);
        $this->tracking->addTracking($item);
        return $this;
    }

    /**
     * Ecommerce Transaction
     */
    public function ecommerceTransaction($options)
    {
        $item = new \Racecore\GATracking\Tracking\Ecommerce\Transaction();
        $item = $this->_fillItem($item, $options);
        $this->tracking->addTracking($item);
        return $this;
    }

    /**
     * Ecommerce Item
     */
    public function ecommerceItem($options)
    {
        $item = new \Racecore\GATracking\Tracking\Ecommerce\Item();
        $item = $this->_fillItem($item, $options);
        $this->tracking->addTracking($item);
        return $this;
    }

    /**
     * Page
     */
    public function page($options)
    {
        $item = new \Racecore\GATracking\Tracking\Page();
        $item = $this->_fillItem($item, $options);
        $this->tracking->addTracking($item);
        return $this;
    }

    /**
     * Event
     */
    public function event($options)
    {
        $item = new \Racecore\GATracking\Tracking\Event();
        $item = $this->_fillItem($item, $options);
        $this->tracking->addTracking($item);
        return $this;
    }

    /**
     * Social
     */
    public function social($options)
    {
        $item = new \Racecore\GATracking\Tracking\Social();
        $item = $this->_fillItem($item, $options);
        $this->tracking->addTracking($item);
        return $this;
    }

    /**
     * App Event
     */
    public function appEvent($options)
    {
        $item = new \Racecore\GATracking\Tracking\App\Event();
        $item = $this->_fillItem($item, $options);
        $this->tracking->addTracking($item);
        return $this;
    }

    /**
     * App Screen
     */
    public function appScreen($options)
    {
        $item = new \Racecore\GATracking\Tracking\App\Screen();
        $item = $this->_fillItem($item, $options);
        $this->tracking->addTracking($item);
        return $this;
    }

    /**
     * User Timing
     */
    public function userTiming($options)
    {
        $item = new \Racecore\GATracking\Tracking\User\Timing();
        $item = $this->_fillItem($item, $options);
        $this->tracking->addTracking($item);
        return $this;
    }

    /**
     * Exception
     */
    public function exception($options)
    {
        $item = new \Racecore\GATracking\Tracking\Exception();
        $item = $this->_fillItem($item, $options);
        $this->tracking->addTracking($item);
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
            Craft::log('Couldn’t send tracking: '.$e->getMessage(), LogLevel::Error);
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
