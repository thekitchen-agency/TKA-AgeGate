<?php

namespace thekitchenagency\craftagegate\records;

use Craft;
use craft\db\ActiveRecord;
use craft\helpers\StringHelper;

class SettingsRecord extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%craftagegate_settings}}';
    }

    public function init()
    {
        parent::init();
        
        // Set default values for new records
        if ($this->isNewRecord) {
            $this->isAgeGateEnabled = false;
            $this->displayType = 'modal';
            $this->validationType = 'simple';
            $this->validationInputStyle = 'single';
            $this->birthdateFormat = 'dmy';
            $this->primaryColor = '#ff6b6b';
            $this->backgroundColor = 'rgba(255, 255, 255, 0.25)';
            $this->blurAmount = '16px';
            $this->borderRadius = '24px';
            $this->minimumAgeAllowed = 18;
            $this->cookieName = 'agegate';
            $this->declineUrl = 'https://www.google.com/';
            $this->agegateTitle = 'Age Gate';
            $this->agegateContent = 'Please enter your date of birth to confirm you are of legal drinking age in your country.';
            $this->agegateAgreeButton = 'Accept';
            $this->agegateDeclineButton = 'Decline';
        }
    }
}
