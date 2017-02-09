<?php
/**
 * @link      https://dukt.net/craft/analytics/
 * @copyright Copyright (c) 2016, Dukt
 * @license   https://dukt.net/craft/analytics/docs/license
 */

namespace Craft;

class Analytics_ReportsController extends BaseController
{
	// Public Methods
	// =========================================================================

	/**
	 * Get Real-Time Report
	 *
	 * @return null
	 */
	public function actionRealtimeWidget()
	{
		$newVisitor = 0;
		$returningVisitor = 0;
		$total = 0;

		if(!craft()->config->get('demoMode', 'analytics'))
		{
			try
			{
				$criteria = new Analytics_RequestCriteriaModel;
				$criteria->realtime = true;
				$criteria->metrics = 'ga:activeVisitors';
				$criteria->optParams = array('dimensions' => 'ga:visitorType');

				$response = craft()->analytics_api->sendRequest($criteria);


				// total

				if(!empty($response['totalResults']))
				{
					$total = $response['totalResults'];
				}


				// new & returning visitors

				if(!empty($response['rows']))
				{
					$rows = $response['rows'];

					if(!empty($rows[0][1]))
					{
						switch($rows[0][0])
						{
							case "RETURNING":
								$returningVisitor = $rows[0][1];
								break;

							case "NEW":
								$newVisitor = $rows[0][1];
								break;
						}
					}

					if(!empty($rows[1][1]))
					{
						switch($rows[1][0])
						{
							case "RETURNING":
								$returningVisitor = $rows[1][1];
								break;

							case "NEW":
								$newVisitor = $rows[1][1];
								break;
						}
					}
				}
			}
            catch(\Google_Service_Exception $e)
            {
                $errors = $e->getErrors();
                $errorMsg = $e->getMessage();

                if(isset($errors[0]['message']))
                {
                    $errorMsg = $errors[0]['message'];
                }

                AnalyticsPlugin::log('Couldn’t get realtime widget data: '.$errorMsg."\r\n".print_r($errors, true), LogLevel::Error);

                $this->returnErrorJson($errorMsg);
            }
            catch(\Exception $e)
            {
                $errorMsg = $e->getMessage();
                AnalyticsPlugin::log('Couldn’t get element data: '.$errorMsg, LogLevel::Error);
                $this->returnErrorJson($errorMsg);
            }
		}
		else
		{
			// Demo Mode
			$newVisitor = 5;
			$returningVisitor = 7;
			$total = ($newVisitor + $returningVisitor);
		}

		$this->returnJson(array(
			'total' => $total,
			'newVisitor' => $newVisitor,
			'returningVisitor' => $returningVisitor
		));
	}

	/**
	 * Get report
	 *
	 * @return null
	 */
	public function actionReportWidget()
	{
		try
		{
			$profileId = craft()->analytics->getProfileId();

			$request = [
				'chart' => craft()->request->getPost('chart'),
				'period' => craft()->request->getPost('period'),
				'options' => craft()->request->getPost('options'),
			];

			$cacheId = ['getReport', $request, $profileId];

			$response = craft()->analytics_cache->get($cacheId);

			if(!$response)
			{
				$response = craft()->analytics_reports->getReport($request);

				if($response)
				{
					craft()->analytics_cache->set($cacheId, $response);
				}
			}

			$this->returnJson($response);
		}
		catch(\Google_Service_Exception $e)
		{
            $errors = $e->getErrors();
            $errorMsg = $e->getMessage();

            if(isset($errors[0]['message']))
            {
                $errorMsg = $errors[0]['message'];
            }

            AnalyticsPlugin::log('Couldn’t get report widget data: '.$errorMsg."\r\n".print_r($errors, true), LogLevel::Error);

            $this->returnErrorJson($errorMsg);
		}
        catch(\Exception $e)
        {
            $errorMsg = $e->getMessage();
            AnalyticsPlugin::log('Couldn’t get element data: '.$errorMsg, LogLevel::Error);
            $this->returnErrorJson($errorMsg);
        }
	}

	/**
	 * Get Element Report
	 *
	 * @param array $variables
	 *
	 * @return null
	 */
	public function actionElement(array $variables = array())
	{
		try
		{
			$elementId = craft()->request->getRequiredParam('elementId');
			$locale = craft()->request->getRequiredParam('locale');
			$metric = craft()->request->getRequiredParam('metric');

			$uri = craft()->analytics->getElementUrlPath($elementId, $locale);

			if($uri)
			{
				if($uri == '__home__')
				{
					$uri = '';
				}

				$start = date('Y-m-d', strtotime('-1 month'));
				$end = date('Y-m-d');
				$dimensions = 'ga:date';

				$optParams = array(
					'dimensions' => $dimensions,
					'filters' => "ga:pagePath==".$uri
				);

				$criteria = new Analytics_RequestCriteriaModel;
				$criteria->startDate = $start;
				$criteria->endDate = $end;
				$criteria->metrics = $metric;
				$criteria->optParams = $optParams;

				$cacheId = ['ReportsController.actionGetElementReport', $criteria->getAttributes()];
				$response = craft()->analytics_cache->get($cacheId);

				if(!$response)
				{
					$response = craft()->analytics_api->sendRequest($criteria);

					if($response)
					{
						craft()->analytics_cache->set($cacheId, $response);
					}
				}

				$this->returnJson([
					'type' => 'area',
					'chart' => $response
				]);
			}
			else
			{
			   throw new Exception("Element doesn't support URLs.", 1);
			}
		}
        catch(\Google_Service_Exception $e)
        {
            $errors = $e->getErrors();
            $errorMsg = $e->getMessage();

            if(isset($errors[0]['message']))
            {
                $errorMsg = $errors[0]['message'];
            }

            AnalyticsPlugin::log('Couldn’t get element data: '.$errorMsg."\r\n".print_r($errors, true), LogLevel::Error);

            $this->returnErrorJson($errorMsg);
        }
		catch(\Exception $e)
		{
            $errorMsg = $e->getMessage();
            AnalyticsPlugin::log('Couldn’t get element data: '.$errorMsg, LogLevel::Error);
			$this->returnErrorJson($errorMsg);
		}
	}
}
