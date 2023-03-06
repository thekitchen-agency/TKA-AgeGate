<?php


namespace thekitchenagency\craftagegate\models;

use Craft;
use craft\base\Model;

/**
 * Agegate settings
 */
class Settings extends Model {
	// Public Properties
	// =========================================================================

	/**
	 * @var string
	 */
	public $pluginName = 'AgeGate';
	public $displayType = 'modal';
	public $isAgeGateEnabled = true;
	public $minimumAgeAllowed = 10;
	public $agegateTitle = 'Age Verification';
	public $agegateContent = 'Verify your age';
	public $dayPlaceHolder = 'DD';
	public $monthPlaceHolder = 'MM';
	public $yearPlaceHolder = 'YYYY';
	public $ageVerificationPath= 'agegate';
	public $cookieName = 'tester';
	public $declineUrl = 'https://www.google.com';
	public $pagePrivacy = [];
	public $pageCookie = [];
	public $pageRedirection = [];

	public $agegateAgreeButton = 'Enter';
	public $agegateDeclineButton = 'Leave';
	public $failureMessage = 'You are not old enough to enter this site';

	// Public Methods
	// =========================================================================

	/**
	 * @inheritdoc
	 */
	public function rules(): array {
		return [
		];
	}
}
