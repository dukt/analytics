<?php

namespace dukt\analytics\migrations;

use craft\db\Migration;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m170324_000002_remove_old_widgets extends Migration
{
    /**
     * Any migration code in here is wrapped inside of a transaction.
     *
     * @return bool
     */
    public function safeUp()
    {
        $this->delete('{{%widgets}}', ['type' => 'Analytics_Chart']);
        $this->delete('{{%widgets}}', ['type' => 'Analytics_Stats']);
        $this->delete('{{%widgets}}', ['type' => 'Analytics_Reports']);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m170324_000002_remove_old_widgets cannot be reverted.\n";

        return false;
    }
}
