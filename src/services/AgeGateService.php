<?php

namespace thekitchenagency\craftagegate\services;

use Craft;
use craft\base\Component;
use craft\web\View;
use thekitchenagency\craftagegate\AgeGate;
use yii\log\Logger;

class AgeGateService extends Component
{
	public function renderAgeGate(): bool {
		$settings = AgeGate::$plugin->getSettings();

		// DISPLAY ONLY ON FRONTEND
		if( !Craft::$app->request->getIsSiteRequest() ) {
			return false;
		}

		if ( !isset($_COOKIE[$settings->cookieName]) && empty($_COOKIE[$settings->cookieName]) ) {
			$html = Craft::$app->view->renderTemplate('_agegate/index.twig', ['settings' => $settings], View::TEMPLATE_MODE_SITE);
			echo $html;
		}

		return true;
	}

	public function getRenderAgeGate() {
		$settings = AgeGate::$plugin->getSettings();
		if( !Craft::$app->request->getIsSiteRequest() ) {
			return false;
		}
		$html = Craft::$app->getView()->renderTemplate('_agegate/index.twig', ['settings' => $settings]);
		echo $html;
	}
}
