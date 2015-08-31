<?php

return array(

    /**
     * Interval at which the realtime widget should refresh its data (in seconds)
     */
    'realtimeRefreshInterval' => 5,

	/**
	 * The amount of time cache should last
	 *
	 * @see http://www.php.net/manual/en/dateinterval.construct.php
	 */
    'cacheDuration' => 'PT15M',

    /**
     * Whether request to APIs should be cached or not
     */
    'enableCache' => true,

    /**
     * Whether Analytics widget is enabled or disabled
     */
    'enableWidget' => true,

    /**
     * Whether Analytics fieldtype is enabled or not
     */
    'enableFieldtype' => true,
);
