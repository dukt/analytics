<?php

return array(

	/**
	 * Interval at which the realtime widget should refresh its data (in seconds)
	 */
	'realtimeRefreshInterval' => 15,

	/**
	 * The amount of time cache should last
	 *
	 * @see http://www.php.net/manual/en/dateinterval.construct.php
	 */
	'cacheDuration' => 'PT10M',

	/**
	 * Whether request to APIs should be cached or not
	 */
	'enableCache' => true,

	/**
	 * Whether Analytics widgets are enabled or disabled
	 */
	'enableWidgets' => true,

	/**
	 * Whether Analytics fieldtype is enabled or not
	 */
	'enableFieldtype' => true,

	/**
	 * Defines global filters applied to every request to the Core Reporting API
	 */
	'filters' => [],
);
