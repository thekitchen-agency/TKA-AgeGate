<?php
namespace thekitchenagency\craftagegate\migrations;

use Craft;
use craft\db\Migration;

class m240517_142600_add_input_style extends Migration
{
    public function safeUp(): bool
    {
        if (!$this->db->columnExists('{{%craftagegate_settings}}', 'validationInputStyle')) {
            $this->addColumn('{{%craftagegate_settings}}', 'validationInputStyle', $this->string()->defaultValue('single'));
        }
        return true;
    }

    public function safeDown(): bool
    {
        if ($this->db->columnExists('{{%craftagegate_settings}}', 'validationInputStyle')) {
            $this->dropColumn('{{%craftagegate_settings}}', 'validationInputStyle');
        }
        return true;
    }
}
