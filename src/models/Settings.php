<?php

namespace thekitchenagency\craftagegate\models;

use Craft;
use craft\base\Model;

/**
 * AgeGate settings
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $pluginName = 'AgeGate';
    public $displayType = 'modal';
    public $isAgeGateEnabled = true;
    public $minimumAgeAllowed = 18;
    public $validationType = 'simple';
    public $validationInputStyle = 'single';
    public $birthdateFormat = 'dmy';
    public $primaryColor = '#ff6b6b';
    public $backgroundColor = 'rgba(255, 255, 255, 0.25)';
    public $blurAmount = '16px';
    public $borderRadius = '24px';
    public $agegateTitle = 'Age Verification';
    public $agegateContent = 'Please enter your date of birth to confirm you are of legal drinking age in your country.';
    public $dayPlaceHolder = 'DD';
    public $monthPlaceHolder = 'MM';
    public $yearPlaceHolder = 'YYYY';
    public $ageVerificationPath = 'agegate';
    public $cookieName = 'agegate';
    public $declineUrl = 'https://www.google.com';
    public $pageRedirection = [];
    public $excludedFromAgeGate = [];
    public $agegateAgreeButton = 'Enter';
    public $agegateDeclineButton = 'Leave';
    public $failureMessage = 'You are not old enough to enter this site';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['pluginName', 'displayType', 'validationType', 'validationInputStyle', 'birthdateFormat', 'primaryColor', 'backgroundColor', 'blurAmount', 'borderRadius', 'agegateTitle', 'agegateContent', 'cookieName', 'declineUrl', 'agegateAgreeButton', 'agegateDeclineButton', 'failureMessage'], 'string'],
            [['isAgeGateEnabled'], 'boolean'],
            [['minimumAgeAllowed'], 'integer', 'min' => 1, 'max' => 100],
            [['pageRedirection', 'excludedFromAgeGate'], 'safe'],
        ];
    }
}
