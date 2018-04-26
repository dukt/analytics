<?php

namespace dukt\analytics\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m141009_105954_analytics_reportsWidgetToExplorerWidget extends Migration
{
    /**
     * Any migration code in here is wrapped inside of a transaction.
     *
     * @return bool
     */
    public function safeUp()
    {
        $widgetResults = (new Query())
            ->select('*')
            ->from(['{{%widgets}}'])
            ->where(['type' => 'Analytics_Reports'])
            ->all();

        if (!empty($widgetResults)) {

            foreach ($widgetResults as $result) {
                $settings = Json::decode($result['settings']);
                $newSettings = [];

                if (!empty($settings['type'])) {
                    switch ($settings['type']) {
                        case 'visits':
                            $newSettings = [
                                'menu' => "audienceOverview",
                                'dimension' => "",
                                'metric' => 'ga:sessions',
                                'chart' => "area",
                                'period' => "month",
                            ];
                            break;

                        case 'geo':
                            $newSettings = [
                                "menu" => "location",
                                "dimension" => "ga:country",
                                "metric" => "ga:pageviewsPerSession",
                                "chart" => "geo",
                                "period" => "month",
                            ];
                            break;

                        case 'mobile':
                            $newSettings = [
                                "menu" => "mobile",
                                "dimension" => "ga:deviceCategory",
                                "metric" => "ga:sessions",
                                "chart" => "pie",
                                "period" => "week",
                            ];
                            break;

                        case 'pages':
                            $newSettings = [
                                "menu" => "allPages",
                                "dimension" => "ga:pagePath",
                                "metric" => "ga:pageviews",
                                "chart" => "table",
                                "period" => "week",
                            ];
                            break;

                        case 'acquisition':
                            $newSettings = [
                                "menu" => "allChannels",
                                "dimension" => "ga:channelGrouping",
                                "metric" => "ga:sessions",
                                "chart" => "table",
                                "period" => "week",
                            ];
                            break;

                        case 'technology':
                            $newSettings = [
                                "menu" => "browserOs",
                                "dimension" => "ga:browser",
                                "metric" => "ga:sessions",
                                "chart" => "pie",
                                "period" => "week",
                            ];
                            break;

                        case 'conversions':
                            $newSettings = [
                                "menu" => "goals",
                                "dimension" => "ga:goalCompletionLocation",
                                "metric" => "ga:goalCompletionsAll",
                                "chart" => "area",
                                "period" => "week",
                            ];
                            break;

                        case 'counts':
                        case 'custom':
                        case 'realtime':
                            $newSettings = [
                                'menu' => "audienceOverview",
                                'dimension' => "",
                                'metric' => 'ga:sessions',
                                'chart' => "area",
                                'period' => "month",
                            ];
                            break;
                    }


                    // Update rows

                    $newSettings = Json::encode($newSettings);

                    $this->update('{{%widgets}}', ['type' => 'Analytics_Explorer', 'settings' => $newSettings], ['id' => $result['id']]);
                }
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m141009_105954_analytics_reportsWidgetToExplorerWidget cannot be reverted.\n";

        return false;
    }
}
