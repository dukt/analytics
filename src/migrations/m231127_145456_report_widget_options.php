<?php

namespace dukt\analytics\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;

/**
 * m231127_145456_report_widget_options migration.
 */
class m231127_145456_report_widget_options extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Store options in `options` instead of `options[chartType]` for the report widget
        $widgetRows = (new Query())
            ->select(['id', 'settings'])
            ->from(['{{%widgets}}'])
            ->where(['type' => 'dukt\analytics\widgets\Report'])
            ->all();

        foreach ($widgetRows as $widgetRow) {
            $widgetSettings = Json::decodeIfJson($widgetRow['settings']);

            if (
                is_array($widgetSettings)
                && $widgetSettings['chart']
                && isset($widgetSettings['options'])
            ) {
                if (isset($widgetSettings['options'][$widgetSettings['chart']])) {
                    $widgetSettings['options'] = $widgetSettings['options'][$widgetSettings['chart']];
                } else {
                    $widgetSettings['options'] = [];
                }

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
        echo "m231127_145456_report_widget_options cannot be reverted.\n";
        return false;
    }
}
