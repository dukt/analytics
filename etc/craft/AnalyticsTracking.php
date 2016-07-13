<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
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
		if(!empty($options['accountId']))
		{
			$accountId = $options['accountId'];

			$this->tracking = new \Racecore\GATracking\GATracking($accountId, array(
				// 'client_create_random_id' => true, // create a random client id when the class can't fetch the current cliend id or none is provided by "client_id"
				// 'client_fallback_id' => 555, // fallback client id when cid was not found and random client id is off
				// 'client_id' => null,    // override client id
				// 'user_id' => null,  // determine current user id

				// // adapter options
				// 'adapter' => array(
				//     'async' => true, // requests to google are async - don't wait for google server response
				//     'ssl' => false // use ssl connection to google server
				// )

				// // use proxy
				// 'proxy' => array(
				//    'ip' => '127.0.0.1', // override the proxy ip with this one
				//    'user_agent' => 'override agent' // override the proxy user agent
				// )
			));
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
			AnalyticsPlugin::log('Couldn’t send tracking: '.$e->getMessage(), LogLevel::Error);
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
