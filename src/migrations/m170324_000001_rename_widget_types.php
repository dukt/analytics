<?php

namespace dukt\analytics\migrations;

use craft\db\Migration;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m170324_000001_rename_widget_types extends Migration
{
    /**
     * Any migration code in here is wrapped inside of a transaction.
     *
     * @return bool
     */
    public function safeUp()
    {
        $this->update('{{%widgets}}', ['type' => 'dukt\analytics\widgets\Report'], ['type' => 'Analytics_Report']);
        $this->update('{{%widgets}}', ['type' => 'dukt\analytics\widgets\Realtime'], ['type' => 'Analytics_Realtime']);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m170324_000001_rename_widget_types cannot be reverted.\n";

        return false;
    }
}
