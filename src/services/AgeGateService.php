<?php

namespace thekitchenagency\craftagegate\services;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;
use thekitchenagency\craftagegate\AgeGate;

class AgeGateService extends Component
{
	public function requireAge($age = null) {
		$request = Craft::$app->getRequest();
		$session = Craft::$app->getSession();
		$response = Craft::$app->getResponse();
		$settings = AgeGate::$settings;

		/*

		if ($settings->isAgeGateEnabled) {
			$age = $age ?? $settings->minimumAgeAllowed;

			$session = Craft::$app->getSession();
			$session->set('age', $age);
			$session->set('agegate', true);
		}*/
	}
}
