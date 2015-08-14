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
        craft()->templates->includeCssResource('analytics/css/AnalyticsRealtimeWidget.css');

        return craft()->templates->render('analytics/widgets/realtime');
    }

    public function getColSpan()
    {
        return 1;
    }
}
