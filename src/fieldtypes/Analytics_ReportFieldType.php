<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_ReportFieldType extends BaseFieldType
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
		return Craft::t('Analytics Report');
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
	public function getInputHtml($name, $value)
	{
		if(craft()->analytics->checkPluginRequirements())
		{
			if(craft()->config->get('enableFieldtype', 'analytics'))
			{
				$plugin = craft()->plugins->getPlugin('analytics');

				// Reformat the input name into something that looks more like an ID
				$id = craft()->templates->formatInputId($name);

				// Figure out what that ID is going to look like once it has been namespaced
				$namespacedId = craft()->templates->namespaceInputId($id);

				$variables = array();

				if($this->element->uri)
				{
					$uri = craft()->analytics->getElementUrlPath($this->element->id, $this->element->locale);

					$ids = craft()->analytics->getProfileId();

					$startDate = date('Y-m-d', strtotime('-1 month'));
					$endDate = date('Y-m-d');
					$metrics = 'ga:pageviews';
					$dimensions = 'ga:date';

					$optParams = array(
						'dimensions' => $dimensions,
						'filters' => "ga:pagePath==".$uri
					);

					$criteria = new Analytics_RequestCriteriaModel;
					$criteria->startDate = $startDate;
					$criteria->endDate = $endDate;
					$criteria->metrics = $metrics;
					$criteria->optParams = $optParams;

					$options = [];

					$cacheId = ['ReportsController.actionGetElementReport', $criteria->getAttributes()];
					$response = craft()->analytics_cache->get($cacheId);

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

					craft()->templates->includeJsResource('analytics/js/jsapi.js', true);
					craft()->templates->includeJsResource('analytics/js/ReportField.js');
					craft()->templates->includeCssResource('analytics/css/ReportField.css');

					craft()->templates->includeJs('var AnalyticsChartLanguage = "'.Craft::t('analyticsChartLanguage').'";');
					craft()->templates->includeJs('new AnalyticsReportField("'.$namespacedId.'-field", '.$jsonOptions.');');

					$variables = array(
						'isNew'   => false,
						'hasUrl'  => true,
						'id'      => $id,
						'uri'     => $uri,
						'name'    => $name,
						'value'   => $value,
						'model'   => $this->model,
						'element' => $this->element
					);
				}
				elseif(!$this->element->id)
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

				return craft()->templates->render('analytics/_components/fieldtypes/Report/input', $variables);
			}
			else
			{
				return craft()->templates->render('analytics/_components/fieldtypes/Report/disabled');
			}
		}
		else
		{
			return craft()->templates->render('analytics/_special/plugin-not-configured');
		}
	}
}
