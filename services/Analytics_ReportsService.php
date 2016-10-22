<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_ReportsService extends BaseApplicationComponent
{
	// Public Methods
	// =========================================================================

	/**
	 * Returns a report for any chart type (Area,  Counter,  Pie,  Table,  Geo)
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	public function getReport($options)
	{
		$chart = $options['chart'];

		$methodName = 'get'.ucfirst($chart).'Report';

		if(method_exists($this, $methodName))
		{
			return $this->{$methodName}($options);
		}
		else
		{
			throw new Exception("Chart type `".$chart."` not supported.");
		}
	}

	// Private Methods
	// =========================================================================

	/**
	 * Returns an area report
	 *
	 * @param array $requestData
	 *
	 * @return array
	 */
	private function getAreaReport($requestData)
	{
		$period = (isset($requestData['period']) ? $requestData['period'] : null);
		$dimension = (isset($requestData['options']['dimension']) ? $requestData['options']['dimension'] : null);
		$metric = (isset($requestData['options']['metric']) ? $requestData['options']['metric'] : null);

		switch($period)
		{
			case 'year':
				$chartDimension = 'ga:yearMonth';
				$start = date('Y-m-01', strtotime('-1 '.$period));
				$end = date('Y-m-d');
				break;

			default:
				$chartDimension = 'ga:date';
				$start = date('Y-m-d', strtotime('-1 '.$period));
				$end = date('Y-m-d');
		}


		// Chart

		$criteria = new Analytics_RequestCriteriaModel;
		$criteria->startDate = $start;
		$criteria->endDate = $end;
		$criteria->metrics = $metric;

		$criteria->optParams = array(
			'dimensions' => $chartDimension,
			'sort' => $chartDimension
		);

		if($dimension)
		{
			$criteria->optParams['filters'] = $dimension.'!=(not set);'.$dimension.'!=(not provided)';
		}

		$chartResponse = craft()->analytics_api->sendRequest($criteria);


		// Total

		$total = 0;

		$totalCriteria = new Analytics_RequestCriteriaModel;
		$totalCriteria->startDate = $start;
		$totalCriteria->endDate = $end;
		$totalCriteria->metrics = $metric;

		if(isset($criteria->optParams['filters']))
		{
			$totalCriteria->optParams = array('filters' => $criteria->optParams['filters']);
		}

		$response = craft()->analytics_api->sendRequest($totalCriteria);

		if(!empty($response['rows'][0][0]['f']))
		{
			$total = $response['rows'][0][0]['f'];
		}


		// Return JSON

		return [
			'type' => 'area',
			'chart' => $chartResponse,
			'total' => $total,
			'metric' => Craft::t(craft()->analytics_metadata->getDimMet($metric)),
			'period' => $period,
			'periodLabel' => Craft::t('This '.$period)
		];
	}

	/**
	 * Returns a counter report
	 *
	 * @param array $requestData
	 *
	 * @return array
	 */
	private function getCounterReport($requestData)
	{
		$period = (isset($requestData['period']) ? $requestData['period'] : null);
		$dimension = (isset($requestData['options']['dimension']) ? $requestData['options']['dimension'] : null);
		$metric = (isset($requestData['options']['metric']) ? $requestData['options']['metric'] : null);

		$start = date('Y-m-d', strtotime('-1 '.$period));
		$end = date('Y-m-d');


		// Counter

		$criteria = new Analytics_RequestCriteriaModel;
		$criteria->startDate = $start;
		$criteria->endDate = $end;
		$criteria->metrics = $metric;

		if($dimension)
		{
			$optParams = array('filters' => $dimension.'!=(not set);'.$dimension.'!=(not provided)');
			$criteria->optParams = $optParams;
		}

		$response = craft()->analytics_api->sendRequest($criteria);

		if(!empty($response['rows'][0][0]))
		{
			$count = $response['rows'][0][0];
		}
		else
		{
			$count = 0;
		}

		$counter = array(
			'type' => $response['cols'][0]['type'],
			'value' => $count,
			'label' => StringHelper::toLowerCase(Craft::t(craft()->analytics_metadata->getDimMet($metric)))
		);


		// Return JSON

		return [
			'type' => 'counter',
			'counter' => $counter,
			'response' => $response,
			'metric' => Craft::t(craft()->analytics_metadata->getDimMet($metric)),
			'period' => $period,
			'periodLabel' => Craft::t('this '.$period)
		];
	}

	/**
	 * Returns a pie report
	 *
	 * @param array $requestData
	 *
	 * @return array
	 */
	private function getPieReport($requestData)
	{
		$period = (isset($requestData['period']) ? $requestData['period'] : null);
		$dimension = (isset($requestData['options']['dimension']) ? $requestData['options']['dimension'] : null);
		$metric = (isset($requestData['options']['metric']) ? $requestData['options']['metric'] : null);

		$start = date('Y-m-d', strtotime('-1 '.$period));
		$end = date('Y-m-d');

		$criteria = new Analytics_RequestCriteriaModel;
		$criteria->startDate = $start;
		$criteria->endDate = $end;
		$criteria->metrics = $metric;

		$criteria->optParams = array(
			'dimensions' => $dimension,
			'sort' => '-'.$metric,
			'max-results' => 20,
			'filters' => $dimension.'!=(not set);'.$dimension.'!=(not provided)'
		);

		$tableResponse = craft()->analytics_api->sendRequest($criteria);

		return [
			'type' => 'pie',
			'chart' => $tableResponse,
			'dimension' => Craft::t(craft()->analytics_metadata->getDimMet($dimension)),
			'metric' => Craft::t(craft()->analytics_metadata->getDimMet($metric)),
			'period' => $period,
			'periodLabel' => Craft::t('this '.$period)
		];
	}

	/**
	 * Returns a table report
	 *
	 * @param array $requestData
	 *
	 * @return array
	 */
	private function getTableReport($requestData)
	{
		$period = (isset($requestData['period']) ? $requestData['period'] : null);
		$dimension = (isset($requestData['options']['dimension']) ? $requestData['options']['dimension'] : null);
		$metric = (isset($requestData['options']['metric']) ? $requestData['options']['metric'] : null);

		$start = date('Y-m-d', strtotime('-1 '.$period));
		$end = date('Y-m-d');

		$criteria = new Analytics_RequestCriteriaModel;
		$criteria->startDate = $start;
		$criteria->endDate = $end;
		$criteria->metrics = $metric;

		$criteria->optParams = array(
			'dimensions' => $dimension,
			'sort' => '-'.$metric,
			'max-results' => 20,
			'filters' => $dimension.'!=(not set);'.$dimension.'!=(not provided)'
		);

		$tableResponse = craft()->analytics_api->sendRequest($criteria);

		return [
			'type' => 'table',
			'chart' => $tableResponse,
			'dimension' => Craft::t(craft()->analytics_metadata->getDimMet($dimension)),
			'metric' => Craft::t(craft()->analytics_metadata->getDimMet($metric)),
			'period' => $period,
			'periodLabel' => Craft::t('this '.$period)
		];
	}

	/**
	 * Returns a geo report
	 *
	 * @param array $requestData
	 *
	 * @return array
	 */
	private function getGeoReport($requestData)
	{
		$period = (isset($requestData['period']) ? $requestData['period'] : null);
		$dimension = (isset($requestData['options']['dimension']) ? $requestData['options']['dimension'] : null);
		$metric = (isset($requestData['options']['metric']) ? $requestData['options']['metric'] : null);

		$start = date('Y-m-d', strtotime('-1 '.$period));
		$end = date('Y-m-d');

		$originDimension = $dimension;

		if($dimension == 'ga:city')
		{
			$dimension = 'ga:latitude, ga:longitude,'.$dimension;
		}


		$criteria = new Analytics_RequestCriteriaModel;
		$criteria->metrics = $metric;

		$criteria->startDate = $start;
		$criteria->endDate = $end;
		$criteria->optParams = array(
			'dimensions' => $dimension,
			'sort' => '-'.$metric,
			'max-results' => 20,
			'filters' => $originDimension.'!=(not set);'.$originDimension.'!=(not provided)',
		);

		$tableResponse = craft()->analytics_api->sendRequest($criteria);

		return [
			'type' => 'geo',
			'chart' => $tableResponse,
			'dimensionRaw' => $originDimension,
			'dimension' => Craft::t(craft()->analytics_metadata->getDimMet($originDimension)),
			'metric' => Craft::t(craft()->analytics_metadata->getDimMet($metric)),
			'period' => $period,
			'periodLabel' => Craft::t('this '.$period)
		];
	}

}
