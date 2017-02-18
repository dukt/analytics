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
		return Craft::t('app', 'Analytics Report');
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

        if(Craft::$app->config->get('enableFieldtype', 'analytics'))
        {
            $plugin = Craft::$app->plugins->getPlugin('analytics');

            // Reformat the input name into something that looks more like an ID
            $id = Craft::$app->getView()->formatInputId($name);

            // Figure out what that ID is going to look like once it has been namespaced
            $namespacedId = Craft::$app->getView()->namespaceInputId($id);

            $variables = array();

            if($element->uri)
            {
                $uri = Analytics::$plugin->analytics->getElementUrlPath($element->id, $element->locale);

                $ids = Analytics::$plugin->analytics->getProfileId();

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
                $response = Analytics::$plugin->analytics_cache->get($cacheId);

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

                /*Craft::$app->getView()->registerJsFile('analytics/js/jsapi.js', true);
                Craft::$app->getView()->registerJsFile('analytics/js/ReportField.js');
                Craft::$app->getView()->registerCssFile('analytics/css/ReportField.css');*/

                Craft::$app->getView()->registerAssetBundle(ReportFieldAsset::class);

                Craft::$app->getView()->registerJs('var AnalyticsChartLanguage = "'.Craft::t('app', 'analyticsChartLanguage').'";');
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
}
