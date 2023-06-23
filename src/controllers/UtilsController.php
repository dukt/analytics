<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) Dukt
 * @license   https://github.com/dukt/analytics/blob/master/LICENSE.md
 */

namespace dukt\analytics\controllers;

use Craft;
use craft\helpers\FileHelper;
use craft\web\Controller;
use dukt\analytics\Plugin as Analytics;
use Google\Service\Analytics\Column;
use yii\web\Response;

/**
 * Class UtilsController
 *
 * @package dukt\analytics\controllers
 */
class UtilsController extends Controller
{
    // Public Methods
    // =========================================================================

    /**
     * Index
     *
     * @return Response
     */
    public function actionIndex(): Response {
        return $this->renderTemplate('analytics/utils/_index');
    }
}