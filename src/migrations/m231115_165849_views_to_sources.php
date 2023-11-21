<?php

namespace dukt\analytics\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;

/**
 * m231115_165849_views_to_sources migration.
 */
class m231115_165849_views_to_sources extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Sources table
        $this->renameTable('{{%analytics_views}}', '{{%analytics_sources}}');
        $this->addColumn('{{%analytics_sources}}', 'type', $this->enum('type', ['UA', 'GA4'])->notNull()->after('name'));
        $this->renameColumn('{{%analytics_sources}}', 'gaViewCurrency', 'gaCurrency');
        $this->alterColumn('{{%analytics_sources}}', 'gaViewId', $this->string()->null());
        $this->alterColumn('{{%analytics_sources}}', 'gaViewName', $this->string()->null());
        $this->alterColumn('{{%analytics_sources}}', 'gaCurrency', $this->string()->null());

        // Site sources table
        $this->renameTable('{{%analytics_site_views}}', '{{%analytics_site_sources}}');
        $this->renameColumn('{{%analytics_site_sources}}', 'viewId', 'sourceId');

        // Rename `viewId` to `sourceId` in widgets
        $widgetRows = (new Query())
            ->select(['id', 'settings'])
            ->from(['{{%widgets}}'])
            ->where(['type' => 'dukt\analytics\widgets\Report'])
            ->orWhere(['type' => 'dukt\analytics\widgets\Realtime'])
            ->orWhere(['type' => 'dukt\analytics\widgets\Ecommerce'])
            ->all();

        foreach ($widgetRows as $widgetRow) {
            $widgetSettings = Json::decodeIfJson($widgetRow['settings']);

            if (is_array($widgetSettings) && isset($widgetSettings['viewId'])) {
                $widgetSettings['sourceId'] = $widgetSettings['viewId'];
                unset($widgetSettings['viewId']);

                $this->update('{{%widgets}}', [
                    'settings' => Json::encode($widgetSettings),
                ], ['id' => $widgetRow['id']]);
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m231115_165849_views_to_sources cannot be reverted.\n";
        return false;
    }
}
