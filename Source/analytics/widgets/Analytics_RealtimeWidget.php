<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2015, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_RealtimeWidget extends BaseWidget
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritDoc IComponentType::getName()
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('Analytics Realtime');
    }

    public function getBodyHtml()
    {
        // craft()->templates->includeJsResource('analytics/js/jsapi.js', true);
        // craft()->templates->includeJsResource('analytics/lib/jquery.serializeJSON/jquery.serializejson.min.js');

        $realtimeRefreshInterval = craft()->config->get('realtimeRefreshInterval', 'analytics');

        $widgetId = $this->model->id;
        $jsonOptions = json_encode([]);

        craft()->templates->includeJsResource('analytics/js/Analytics.js');
        craft()->templates->includeJsResource('analytics/js/AnalyticsRealtimeWidget.js');
        craft()->templates->includeCssResource('analytics/css/AnalyticsRealtimeWidget.css');

        craft()->templates->includeJs('var AnalyticsChartLanguage = "'.craft()->analytics->getLanguage().'";', true);
        craft()->templates->includeJs('var AnalyticsRealtimeInterval = "'.$realtimeRefreshInterval.'";', true);

        craft()->templates->includeJs('new Analytics.Realtime("widget'.$widgetId.'", '.$jsonOptions.');');

        return craft()->templates->render('analytics/widgets/realtime');
    }

    public function getColSpan()
    {
        return 1;
    }
}
