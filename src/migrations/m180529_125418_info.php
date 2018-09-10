<?php

namespace dukt\analytics\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;
use dukt\analytics\models\Info;
use dukt\analytics\Plugin;

/**
 * m180529_125418_info migration.
 */
class m180529_125418_info extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTables();
        $this->insertDefaultData();
    }

    /**
     * Creates the tables.
     */
    public function createTables()
    {
        $this->createTable(
            '{{%analytics_info}}',
            [
                'id' => $this->primaryKey(),
                'forceConnect' => $this->boolean()->defaultValue(false)->notNull(),
                'token' => $this->text(),

                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid()
            ]
        );
    }

    /**
     * Populates the DB with the default data.
     */
    public function insertDefaultData()
    {
        $forceConnect = false;
        $token = null;
        $settings = null;

        $row = (new Query())
            ->select('*')
            ->from(['{{%plugins}}'])
            ->where(['handle' => 'analytics'])
            ->one();

        if ($row) {
            $settingsJson = $row['settings'];
            $settings = Json::decode($settingsJson);
        }

        if($settings) {
            $forceConnect = $settings['forceConnect'];

            if(isset($settings['token'])) {
                $token = $settings['token'];
            }
        }

        // Populate the analytics_info table
        echo '    > populate the analytics_info table ...';
        Plugin::getInstance()->saveInfo(new Info([
            'forceConnect' => $forceConnect,
            'token' => $token,
        ]));
        echo " done\n";

        // Get rid of the old plugin settings
        echo '    > remove old plugin settings ...';
        if($settings) {
            unset($settings['forceConnect']);

            if(isset($settings['token'])) {
                unset($settings['token']);
            }

            $settingsJson = Json::encode($settings);
            $this->update('{{%plugins}}', ['settings' => $settingsJson], ['id' => $row['id']]);
        }
        echo " done\n";
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180529_125418_info cannot be reverted.\n";
        return false;
    }
}
