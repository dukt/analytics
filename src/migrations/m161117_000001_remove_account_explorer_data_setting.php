<?php
namespace dukt\analytics\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m161117_000001_remove_account_explorer_data_setting extends Migration
{
    /**
     * Any migration code in here is wrapped inside of a transaction.
     *
     * @return bool
     */
    public function safeUp()
    {
        // set forceConnect setting to true

        $row = (new Query())
            ->select('*')
            ->from(['{{%plugins}}'])
            ->where(['plugins.handle' => 'analytics'])
            ->one();

        if($row)
        {
            $settingsJson = $row['settings'];

            $settings = Json::decode($settingsJson);

            if(isset($settings['accountExplorerData']))
            {
                unset($settings['accountExplorerData']);
            }

            $settingsJson = Json::encode($settings);

            $this->update('{{%plugins}}', ['settings' => $settingsJson], ['id' => $row['id']]);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m161117_000001_remove_account_explorer_data_setting cannot be reverted.\n";

        return false;
    }
}
