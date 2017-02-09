<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m161021_000001_force_connect extends BaseMigration
{
    /**
     * Any migration code in here is wrapped inside of a transaction.
     *
     * @return bool
     */
    public function safeUp()
    {
        // set forceConnect setting to true

        $row = craft()->db->createCommand()
            ->select('*')
            ->from('plugins')
            ->where('class=:class', array(':class'=>'Analytics'))
            ->queryRow();

        if($row)
        {
            $settingsJson = $row['settings'];

            $settings = JsonHelper::decode($settingsJson);
            $settings['forceConnect'] = true;

            $settingsJson = JsonHelper::encode($settings);

            craft()->db->createCommand()
                ->update('plugins', array('settings' => $settingsJson), 'id=:id', array('id' => $row['id']));
        }

        return true;
    }
}
