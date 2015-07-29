<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_TwitterService extends BaseApplicationComponent
{
    public function table()
    {
        $period = craft()->request->getParam('period');
        $dimension = craft()->request->getParam('dimension'); // sections or elementTypes

        $chartResponse = array(
            'cols' => array(
                array(
                    'dataType' => "STRING",
                    'id' => 'userType',
                    'label' => "User Type",
                    'type' => "string",
                ),
                array(
                    'dataType' => "INTEGER",
                    'id' => "users",
                    'label' => "Users",
                    'type' => "number",
                ),
            ),
            'rows' => array()
        );

        $response = craft()->twitter->get('account/verify_credentials');

        $following = $response['friends_count'];
        $followers = $response['followers_count'];

        $chartResponse['rows'][] = [
            ['v' => "Following", 'f' => "Following"],
            ['v' => $following, 'f' => (string) $following],
        ];

        $chartResponse['rows'][] = [
            ['v' => "Followers", 'f' => "Followers"],
            ['v' => $followers, 'f' => (string) $followers],
        ];

        return [
            'table' => $chartResponse,
            'total' => 0,
            'dimension' => 'ga:users',
            'metric' => 'ga:users',
            'period' => $period,
            'periodLabel' => Craft::t('this '.$period)
        ];
    }
}
