<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m130623_011710_analytics_CreateSettingsTable extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
    public function safeUp()
    {
        $settings = new Analytics_SettingsRecord('install');
        $settings->createTable();
        $settings->addForeignKeys();

        return true;
    }
}
