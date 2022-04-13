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

    public function actionGetReportWidgetSettings()
    {
        $reportingViews = Analytics::$plugin->getViews()->getViews();
        $selectOptions = Analytics::$plugin->getMetadata()->getSelectOptionsByChartType();
        $selectOptionsForJson = [];

        foreach($selectOptions as $chartType => $_selectOptions) {
            foreach($_selectOptions as $dimmetKey => $dimmetOptions) {
                foreach($dimmetOptions as $optionValue => $option) {
                    if (is_array($option) && $option['optgroup']) {
                        $selectOptionsForJson[$chartType][$dimmetKey][] = [
                            'optgroup' => $option['optgroup'],
                        ];
                    } else {
                        $selectOptionsForJson[$chartType][$dimmetKey][] = [
                            'label' => $option,
                            'value' => $optionValue
                        ];
                    }
                }
            }
        }

//        echo '<pre>';
//        var_dump($selectOptions);
//        echo '</pre>';

        return $this->asJson([
            'views' => $reportingViews,
            'selectOptions' => $selectOptionsForJson,
        ]);
    }
}