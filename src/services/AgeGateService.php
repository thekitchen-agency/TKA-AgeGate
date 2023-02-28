<?php

namespace thekitchenagency\craftagegate\services;

use Craft;
use craft\base\Component;
use craft\web\View;
use thekitchenagency\craftagegate\AgeGate;

class AgeGateService extends Component
{
	public function renderAgeGate(): bool {
		$settings = AgeGate::$plugin->getSettings();

		// DISPLAY ONLY ON FRONTEND
		if(Craft::$app->request->getIsCpRequest()
		   || Craft::$app->request->getIsConsoleRequest()
		   || Craft::$app->request->getIsLivePreview()
		   || Craft::$app->request->getIsPreview()
		   || (Craft::$app->request->hasMethod("getIsAjax") && Craft::$app->request->getIsAjax())
		   || (Craft::$app->request->hasMethod("getIsLivePreview") && (Craft::$app->request->getIsLivePreview() && CookieConsentBanner::$plugin->getSettings()->disable_in_live_preview))) {
			return false;
		}

		if ( isset($_COOKIE[$settings->cookieName]) && !empty($_COOKIE[$settings->cookieName]) ) {
			return false;
		} else {
			Craft::$app->view->setTemplateMode(Craft::$app->view::TEMPLATE_MODE_SITE);
			$html = Craft::$app->view->renderTemplate('_agegate/index.twig', ['settings' => $settings], View::TEMPLATE_MODE_SITE);
			echo $html;
		}
		return true;
	}
}
