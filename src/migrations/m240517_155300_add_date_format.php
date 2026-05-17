<?php
namespace thekitchenagency\craftagegate\migrations;

use Craft;
use craft\db\Migration;

class m240517_155300_add_date_format extends Migration
{
    public function safeUp(): bool
    {
        if (!$this->db->columnExists('{{%craftagegate_settings}}', 'birthdateFormat')) {
            $this->addColumn('{{%craftagegate_settings}}', 'birthdateFormat', $this->string()->defaultValue('dmy'));
        }
        return true;
    }

    public function safeDown(): bool
    {
        if ($this->db->columnExists('{{%craftagegate_settings}}', 'birthdateFormat')) {
            $this->dropColumn('{{%craftagegate_settings}}', 'birthdateFormat');
        }
        return true;
    }
}
