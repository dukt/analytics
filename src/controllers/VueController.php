<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\controllers;

use craft\web\Controller;
use dukt\analytics\Plugin as Analytics;

/**
 * Class VueController
 *
 * @package dukt\analytics\controllers
 */
class VueController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionGetReportingViews()
    {
        $reportingViews = Analytics::$plugin->getViews()->getViews();

        return $this->asJson([
            'views' => $reportingViews,
        ]);
    }
}