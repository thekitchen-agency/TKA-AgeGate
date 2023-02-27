<?php


namespace thekitchenagency\craftagegate\models;

use Craft;
use craft\base\Model;

/**
 * Agegate settings
 */
class Settings extends Model {
	public $pluginName = 'AgeGate';
	public $isAgeGateEnabled = true;
	public $minimumAgeAllowed = 10;
	public $agegateTitle = 'Age Verification';
	public $agegateContent = 'Verify your age';
	public $dayPlaceHolder = 'DD';
	public $monthPlaceHolder = 'MM';
	public $yearPlaceHolder = 'YYYY';
	public $ageVerificationPath= 'agegate';

	public $agegateAgreeButton = 'Enter';
	public $agegateDeclineButton = 'Leave';
	public $failureMessage = 'You are not old enough to enter this site';

	public function defineRules(): array {
		return [
		];
	}
}
