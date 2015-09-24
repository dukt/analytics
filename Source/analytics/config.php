<?php

return array(

    /**
     * Interval at which the realtime widget should refresh its data (in seconds)
     *
     * @see http://www.php.net/manual/en/dateinterval.construct.php
     */
    'realtimeRefreshInterval' => 'PT15S',

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
     * Whether Analytics widget is enabled or disabled
     */
    'enableWidget' => true,

    /**
     * Whether Analytics fieldtype is enabled or not
     */
    'enableFieldtype' => true,
);
