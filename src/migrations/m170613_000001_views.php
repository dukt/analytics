<?php

namespace dukt\analytics\migrations;

use craft\db\Migration;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m170613_000001_views extends Migration
{
    /**
     * Any migration code in here is wrapped inside of a transaction.
     *
     * @return bool
     */
    public function safeUp()
    {
        $this->createTable(
            '{{%analytics_views}}',
            [
                'id' => $this->primaryKey(),
                'name' => $this->string()->notNull(),
                'gaAccountId' => $this->string()->notNull(),
                'gaAccountName' => $this->string()->notNull(),
                'gaPropertyId' => $this->string()->notNull(),
                'gaPropertyName' => $this->string()->notNull(),
                'gaViewId' => $this->string()->notNull(),
                'gaViewName' => $this->string()->notNull(),
                'gaViewCurrency' => $this->string()->notNull(),

                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid()
            ]
        );

        $this->createTable(
            '{{%analytics_site_views}}',
            [
                'id' => $this->primaryKey(),
                'siteId' => $this->integer()->notNull(),
                'viewId' => $this->integer(),

                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid()
            ]
        );

        $this->createIndex(null, '{{%analytics_site_views}}', 'siteId,viewId', true);
        $this->addForeignKey($this->db->getForeignKeyName(), '{{%analytics_site_views}}', 'siteId', '{{%sites}}', 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName(), '{{%analytics_site_views}}', 'viewId', '{{%analytics_views}}', 'id', 'CASCADE', null);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m170613_000001_views cannot be reverted.\n";

        return false;
    }
}
