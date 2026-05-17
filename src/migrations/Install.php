<?php

namespace thekitchenagency\craftagegate\migrations;

use Craft;
use craft\db\Migration;

/**
 * Install migration.
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%craftagegate_settings}}');
        if ($tableSchema === null) {
            $this->createTable('{{%craftagegate_settings}}', [
                'id' => $this->primaryKey(),
                'agegateSiteId' => $this->integer()->notNull(),
                'pluginName' => $this->string()->defaultValue('AgeGate'),
                'isAgeGateEnabled' => $this->boolean()->defaultValue(false),
                'displayType' => $this->string()->defaultValue('modal'),
                'validationType' => $this->string()->defaultValue('simple'),
                'validationInputStyle' => $this->string()->defaultValue('single'),
                'birthdateFormat' => $this->string()->defaultValue('dmy'),
                'primaryColor' => $this->string()->defaultValue('#ff6b6b'),
                'backgroundColor' => $this->string()->defaultValue('rgba(255, 255, 255, 0.25)'),
                'blurAmount' => $this->string()->defaultValue('16px'),
                'borderRadius' => $this->string()->defaultValue('24px'),
                'minimumAgeAllowed' => $this->integer()->defaultValue(18),
                'agegateTitle' => $this->string()->defaultValue('Age Verification'),
                'agegateContent' => $this->text(),
                'cookieName' => $this->string()->defaultValue('agegate'),
                'declineUrl' => $this->string()->defaultValue('https://www.google.com/'),
                'agegateAgreeButton' => $this->string()->defaultValue('Accept'),
                'agegateDeclineButton' => $this->string()->defaultValue('Decline'),
                'pageRedirection' => $this->text(),
                'excludedFromAgeGate' => $this->text(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists('{{%craftagegate_settings}}');
        return true;
    }
}
