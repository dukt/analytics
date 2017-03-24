<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2017, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace dukt\analytics\fields;

use Craft;
use craft\base\Field;
use dukt\analytics\models\RequestCriteria;
use dukt\analytics\web\assets\reportfield\ReportFieldAsset;
use dukt\analytics\Plugin as Analytics;

class Report extends Field
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
        return Craft::t('analytics', 'Analytics Report');
    }

    /**
     * @inheritDoc IFieldType::defineContentAttribute()
     *
     * @return mixed
     */
    public function defineContentAttribute()
    {
        return AttributeType::String;
    }

    /**
     * Show field
     */
    public function getInputHtml($value, \craft\base\ElementInterface $element = NULL): string
    {
        $name = $this->handle;

        if(Analytics::$plugin->getAnalytics()->checkPluginRequirements())
        {
            if(Analytics::$plugin->getSettings()->enableFieldtype)
            {
                $plugin = Craft::$app->getPlugins()->getPlugin('analytics');

                // Reformat the input name into something that looks more like an ID
                $id = Craft::$app->getView()->formatInputId($name);

                // Figure out what that ID is going to look like once it has been namespaced
                $namespacedId = Craft::$app->getView()->namespaceInputId($id);

                $variables = array();

                if($element->uri)
                {
                    $uri = Analytics::$plugin->getAnalytics()->getElementUrlPath($element->id, $element->locale);

                    $ids = Analytics::$plugin->getAnalytics()->getProfileId();

                    $startDate = date('Y-m-d', strtotime('-1 month'));
                    $endDate = date('Y-m-d');
                    $metrics = 'ga:pageviews';
                    $dimensions = 'ga:date';

                    $optParams = array(
                        'dimensions' => $dimensions,
                        'filters' => "ga:pagePath==".$uri
                    );

                    $criteria = new RequestCriteria;
                    $criteria->startDate = $startDate;
                    $criteria->endDate = $endDate;
                    $criteria->metrics = $metrics;
                    $criteria->optParams = $optParams;

                    $options = [];

                    $cacheId = ['ReportsController.actionGetElementReport', $criteria->getAttributes()];
                    $response = Analytics::$plugin->cache->get($cacheId);

                    if($response)
                    {
                        $response = [
                            'type' => 'area',
                            'chart' => $response
                        ];

                        $options = [
                            'cachedResponse' => $response
                        ];
                    }

                    $jsonOptions = json_encode($options);

                    Craft::$app->getView()->registerAssetBundle(ReportFieldAsset::class);

                    Craft::$app->getView()->registerJs('var AnalyticsChartLanguage = "'.Craft::t('analytics', 'analyticsChartLanguage').'";');
                    Craft::$app->getView()->registerJs('new AnalyticsReportField("'.$namespacedId.'-field", '.$jsonOptions.');');

                    $variables = array(
                        'isNew'   => false,
                        'hasUrl'  => true,
                        'id'      => $id,
                        'uri'     => $uri,
                        'name'    => $name,
                        'value'   => $value,
                        'model'   => $this,
                        'element' => $element
                    );
                }
                elseif(!$element->id)
                {
                    $variables = array(
                        'hasUrl' => false,
                        'isNew' => true,
                    );
                }
                else
                {
                    $variables = array(
                        'hasUrl' => false,
                        'isNew' => false,
                    );
                }

                return Craft::$app->getView()->renderTemplate('analytics/_components/fieldtypes/Report/input', $variables);
            }
            else
            {
                return Craft::$app->getView()->renderTemplate('analytics/_components/fieldtypes/Report/disabled');
            }
        }
        else
        {
            return Craft::$app->getView()->renderTemplate('analytics/_special/plugin-not-configured');
        }
    }
}
