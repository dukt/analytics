<?php

namespace dukt\analytics\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m170324_000003_fix_widget_options extends Migration
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
            ->where(['type' => \dukt\analytics\widgets\Realtime::class])
            ->orWhere(['type' => \dukt\analytics\widgets\Report::class])
            ->all();

        if ($widgetResults) {
            foreach ($widgetResults as $result) {
                $settings = Json::decode($result['settings']);

                if (isset($settings['options']['metric'])) {
                    $settings['options'] = [
                        $settings['chart'] => $settings['options']
                    ];
                }

                // Update row

                $settings = Json::encode($settings);

                $this->update('{{%widgets}}', ['settings' => $settings], ['id' => $result['id']]);
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m170324_000003_fix_widget_options cannot be reverted.\n";

        return false;
    }
}
