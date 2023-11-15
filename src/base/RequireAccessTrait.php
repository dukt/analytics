<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\base;

use Craft;
use yii\web\ForbiddenHttpException;

trait RequireAccessTrait
{
    public function requirePluginAccess()
    {
        $userSession = Craft::$app->getUser();
        $currentUser = $userSession->getIdentity();

        if(!$currentUser->can('accessPlugin-analytics')) {
            throw new ForbiddenHttpException('User not authorized to save this source.');
        }
    }
}
