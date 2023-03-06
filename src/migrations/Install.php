<?php

namespace thekitchenagency\craftagegate\migrations;

use thekitchenagency\craftagegate\AgeGate;
use Craft;
use craft\config\DbConfig;
use craft\db\Migration;

class Install extends Migration {
	public $driver;

	public function safeUp(): bool {
		$this->driver = Craft::$app->getConfig()->getDb()->driver;
		return $this->createTables();
	}

	public function safeDown(): bool {
		$this->driver = Craft::$app->getConfig()->getDb()->driver;
		$this->removeTables();

		return true;
	}

	protected function createTables() {
		$tablesCreated = false;

		$tableSchema = Craft::$app->db->schema->getTableSchema('{{%craftagegate_settings}}');

		if($tableSchema === null) {
			$tablesCreated = true;
			$this->createTable(
				'{{%craftagegate_settings}}',
				[
					'id' => $this->primaryKey(),
					'agegateSiteId' => $this->integer()->notNull(),
					'isAgeGateEnabled' => $this->boolean()->defaultValue(false),
					'displayType' => $this->string()->defaultValue('modal'),
					'minimumAgeAllowed' => $this->integer()->defaultValue(18),
					'cookieName' => $this->string()->defaultValue('agegate'),
					'declineUrl' => $this->string()->defaultValue(''),
					'agegateTitle' => $this->string()->defaultValue(''),
					'agegateContent' => $this->string()->defaultValue(''),
					'agegateAgreeButton' => $this->string()->defaultValue(''),
					'agegateDeclineButton' => $this->string()->defaultValue(''),
					'pagePrivacy' => $this->string()->defaultValue(''),
					'pageCookie' => $this->string()->defaultValue(''),
					'pageRedirection' => $this->string()->defaultValue('')
				]
			);

			$this->addForeignKey(
				$this->db->getForeignKeyName('{{%craftagegate_settings}}', 'agegateSiteId'),
				'{{%craftagegate_settings}}',
				'agegateSiteId',
				'{{%sites}}',
				'id',
				'CASCADE',
				'CASCADE'
			);
		}

		return $tablesCreated;
	}

	protected function removeTables() {
		$this->dropTableIfExists('{{%craftagegate_settings}}');
	}
}
