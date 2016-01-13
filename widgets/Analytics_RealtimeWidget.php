<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_RealtimeWidget extends BaseWidget
{
    // Public Methods
    // =========================================================================

    public function isSelectable()
    {
        $plugin = craft()->plugins->getPlugin('analytics');
        $settings = $plugin->getSettings();

        if(empty($settings['enableRealtime']))
        {
            return false;
        }

        return parent::isSelectable();
    }

    /**
     * @inheritDoc IWidget::getTitle()
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('Analytics Real-Time');
    }

    /**
     * @inheritDoc IWidget::getIconPath()
     *
     * @return string
     */
    public function getIconPath()
    {
        return craft()->resources->getResourcePath('analytics/images/widgets/realtime.svg');
    }

    /**
     * @inheritDoc IWidget::getBodyHtml()
     *
     * @return string|false
     */
    public function getBodyHtml()
    {
        if(craft()->analytics_plugin->checkRequirements())
        {
            if(craft()->config->get('enableWidgets', 'analytics'))
            {
                $profileId = craft()->analytics->getProfileId();

                if($profileId)
                {
                    $plugin = craft()->plugins->getPlugin('analytics');
                    $settings = $plugin->getSettings();

                    if(!empty($settings['enableRealtime']))
                    {
                        $realtimeRefreshInterval = craft()->analytics->getRealtimeRefreshInterval();

                        $widgetId = $this->model->id;

                        craft()->templates->includeJsResource('analytics/js/Analytics.js');
                        craft()->templates->includeJsResource('analytics/js/AnalyticsRealtimeWidget.js');
                        craft()->templates->includeCssResource('analytics/css/AnalyticsRealtimeWidget.css');

                        craft()->templates->includeJs('var AnalyticsChartLanguage = "'.craft()->language.'";', true);
                        craft()->templates->includeJs('var AnalyticsRealtimeInterval = "'.$realtimeRefreshInterval.'";', true);

                        craft()->templates->includeJs('new Analytics.Realtime("widget'.$widgetId.'");');

                        return craft()->templates->render('analytics/_components/widgets/Realtime/body');
                    }
                    else
                    {
                        return craft()->templates->render('analytics/_components/widgets/Realtime/disabled');
                    }
                }
                else
                {
                    return craft()->templates->render('analytics/_install/plugin-not-configured');
                }
            }
            else
            {
                return craft()->templates->render('analytics/_components/widgets/Realtime/disabled');
            }
        }
        else
        {
            return craft()->templates->render('analytics/_install/plugin-not-configured');
        }
    }

    /**
     * @inheritDoc IWidget::getColspan()
     *
     * @return int
     */
    public function getColSpan()
    {
        return 1;
    }
}
