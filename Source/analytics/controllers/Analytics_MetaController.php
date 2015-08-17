<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_MetaController extends BaseController
{
    public function actionIndex(array $variables = array())
    {
        $variables['test'] = craft()->analytics_meta->getDimensionsOptions();
        $variables['dimensions'] = craft()->analytics_meta->getDimensions();
        $variables['metrics'] = craft()->analytics_meta->getMetrics();

        $this->renderTemplate('analytics/meta', $variables);
    }

    public function actionSearch()
    {
        $q = craft()->request->getParam('q');

        $columns = craft()->analytics_meta->searchColumns($q);

        craft()->urlManager->setRouteVariables(array(
            'q' => $q,
            'columns' => $columns
        ));
    }
}
