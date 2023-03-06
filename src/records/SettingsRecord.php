<?php

namespace thekitchenagency\craftagegate\records;

use Craft;
use craft\db\ActiveRecord;
use craft\helpers\StringHelper;

class SettingsRecord extends ActiveRecord {
	public static function tableName(): string {
		return '{{%craftagegate_settings}}';
	}
}

