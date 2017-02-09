<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m141009_105954_analytics_reportsWidgetToExplorerWidget extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{

		$rows = craft()->db->createCommand()
			->select('*')
			->from('widgets')
			->where('type=:type', array(':type'=>'Analytics_Reports'))
			->queryAll();

		if($rows)
		{
			foreach($rows as $row)
			{
				$settings = JsonHelper::decode($row['settings']);

				if(!empty($settings['type']))
				{
					switch($settings['type'])
					{
						case 'visits':
							$newSettings = array(
								'menu' => "audienceOverview",
								'dimension' => "",
								'metric' => 'ga:sessions',
								'chart' => "area",
								'period' => "month",
							);
							break;

						case 'geo':
							$newSettings = array(
								"menu" => "location",
								"dimension" => "ga:country",
								"metric" => "ga:pageviewsPerSession",
								"chart" => "geo",
								"period" => "month",
							);
							break;

						case 'mobile':
							$newSettings = array(
								"menu" => "mobile",
								"dimension" => "ga:deviceCategory",
								"metric" => "ga:sessions",
								"chart" => "pie",
								"period" => "week",
							);
							break;

						case 'pages':
							$newSettings = array(
								"menu" => "allPages",
								"dimension" => "ga:pagePath",
								"metric" => "ga:pageviews",
								"chart" => "table",
								"period" => "week",
							);
							break;

						case 'acquisition':
							$newSettings = array(
								"menu" => "allChannels",
								"dimension" => "ga:channelGrouping",
								"metric" => "ga:sessions",
								"chart" => "table",
								"period" => "week",
							);
							break;

						case 'technology':
							$newSettings = array(
								"menu" => "browserOs",
								"dimension" => "ga:browser",
								"metric" => "ga:sessions",
								"chart" => "pie",
								"period" => "week",
							);
							break;

						case 'conversions':
							$newSettings = array(
								"menu" => "goals",
								"dimension" => "ga:goalCompletionLocation",
								"metric" => "ga:goalCompletionsAll",
								"chart" => "area",
								"period" => "week",
							);
							break;

						case 'counts':
						case 'custom':
						case 'realtime':
							$newSettings = array(
								'menu' => "audienceOverview",
								'dimension' => "",
								'metric' => 'ga:sessions',
								'chart' => "area",
								'period' => "month",
							);
							break;
					}


					// update rows

					$newSettings = JsonHelper::encode($newSettings);

					$updateCmd = craft()->db->createCommand()
						->update('widgets', array('type' => 'Analytics_Explorer', 'settings' => $newSettings), 'id=:id', array('id' => $row['id']));
				}
			}
		}

		return true;
	}
}
