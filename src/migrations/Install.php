<?php
/**
 * @link      https://dukt.net/analytics/
 * @copyright Copyright (c) 2022, Dukt
 * @license   https://dukt.net/analytics/docs/license
 */

namespace dukt\analytics\migrations;

use Craft;
use craft\db\Migration;
use craft\helpers\MigrationHelper;
use dukt\analytics\models\Info;
use dukt\analytics\Plugin;

class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * This method contains the logic to be executed when applying this migration.
     * This method differs from [[up()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[up()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();
        $this->insertDefaultData();

        return true;
    }

    /**
     * This method contains the logic to be executed when removing this migration.
     * This method differs from [[down()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[down()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeForeignKeys();
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function createTables()
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
     * Creates the indexes needed for the Records used by the plugin
     *
     * @return void
     */
    protected function createIndexes()
    {
        $this->createIndex(null, '{{%analytics_site_views}}', 'siteId,viewId', true);
    }

    /**
     * Creates the foreign keys needed for the Records used by the plugin
     *
     * @return void
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey($this->db->getForeignKeyName(), '{{%analytics_site_views}}', 'siteId', '{{%sites}}', 'id', 'CASCADE', null);
        $this->addForeignKey($this->db->getForeignKeyName(), '{{%analytics_site_views}}', 'viewId', '{{%analytics_views}}', 'id', 'CASCADE', null);
    }

    /**
     * Populates the DB with the default data.
     *
     * @return void
     */
    protected function insertDefaultData()
    {
        // Populate the info table
        echo '    > populate the analytics_info table ...';
        Plugin::getInstance()->saveInfo(new Info());
        echo " done\n";
    }

    /**
     * Removes the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeTables()
    {
        $this->dropTable('{{%analytics_views}}');
        $this->dropTable('{{%analytics_site_views}}');
        $this->dropTable('{{%analytics_info}}');
    }

    /**
     * Removes the foreign keys needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeForeignKeys()
    {
        if ($this->db->tableExists('{{%analytics_site_views}}')) {
            MigrationHelper::dropAllForeignKeysOnTable('{{%analytics_site_views}}');
        }
    }
}
