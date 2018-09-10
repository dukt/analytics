<?php

namespace dukt\analytics\migrations;

use Craft;
use craft\db\Migration;
use dukt\analytics\fields\Report as ReportField;
use dukt\analytics\widgets\Report as ReportWidget;

/**
 * m180910_130202_craft3_upgrade migration.
 */
class m180910_130202_craft3_upgrade extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Fields
        $this->update('{{%fields}}', [
            'type' => ReportField::class
        ], ['type' => 'Analytics_Report']);

        // Widgets
        $this->update('{{%widgets}}', [
            'type' => ReportWidget::class
        ], ['type' => 'Analytics_Report']);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180910_130202_craft3_upgrade cannot be reverted.\n";
        return false;
    }
}
