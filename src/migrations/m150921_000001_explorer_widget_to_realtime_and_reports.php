<?php
namespace dukt\analytics\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m150921_000001_explorer_widget_to_realtime_and_reports extends Migration
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
            ->where(['widgets.type' => 'Analytics_Explorer'])
            ->all();

        if($widgetResults)
        {
            foreach($widgetResults as $result)
            {
                $oldSettings = Json::decode($result['settings']);


                // Old to new

                $newSettings = [];

                if(isset($oldSettings['chart']))
                {
                    $newSettings['chart'] = $oldSettings['chart'];
                }

                if(isset($oldSettings['period']))
                {
                    $newSettings['period'] = $oldSettings['period'];
                }

                $newSettings['options'] = [];

                if(isset($oldSettings['dimension']))
                {
                    $newSettings['options']['dimension'] = $oldSettings['dimension'];
                }

                if(isset($oldSettings['metric']))
                {
                    $newSettings['options']['metric'] = $oldSettings['metric'];
                }

                switch($oldSettings['menu'])
                {
                    case 'realtimeVisitors':
                        $type='Analytics_Realtime';
                        break;

                    default:
                        $type='Analytics_Report';
                }


                // Update row

                $newSettings = Json::encode($newSettings);

                $this->update('{{%widgets}}', ['type' => $type, 'settings' => $newSettings], ['id' => $result['id']]);
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m150921_000001_explorer_widget_to_realtime_and_reports cannot be reverted.\n";

        return false;
    }
}
