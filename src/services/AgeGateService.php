<?php

namespace thekitchenagency\craftagegate\services;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;
use craft\web\View;
use thekitchenagency\craftagegate\records\SettingsRecord;

class AgeGateService extends Component
{
	public static $settings = null;

	public function init(): void {
		self::$settings = $this->getCurrentSiteAgeGateSettings();

		$matchingSite = false;
		if(self::$settings->isAgeGateEnabled && ! $matchingSite  && self::$settings->displayType === 'redirect') {
			if ( Craft::$app->request->getIsSiteRequest() ) {
				if ( self::$settings->isAgeGateEnabled && ! $matchingSite && self::$settings->displayType === 'modal' ) {
					//
				} else if ( self::$settings->isAgeGateEnabled && !$matchingSite && self::$settings->displayType === 'redirect' && Craft::$app->getRequest()->getSegment( 1 ) != 'agegate' ) {
					$originalUrl = Craft::$app->getRequest()->getFullUri();
					Craft::$app->getSession()->set( 'originalSrcUrl', $originalUrl );

					if ( ! isset( $_COOKIE[ self::$settings->cookieName ] ) || empty( $_COOKIE[ self::$settings->cookieName ] ) ) {
						Craft::$app->getResponse()->redirect( UrlHelper::siteUrl( 'agegate' ) )->send();
					}

				} else if ( self::$settings->isAgeGateEnabled && ! $matchingSite && self::$settings->displayType === 'redirect' && Craft::$app->getRequest()->getSegment( 1 ) == 'agegate' ) {
					Craft::$app->getView()->registerJsVar( 'originalSrcUrl', Craft::$app->getSession()->get( 'originalSrcUrl' ) );
				}
			}
		}
	}

	public function getCurrentSiteAgeGateSettings(): SettingsRecord {
		$params = ['agegateSiteId' => Craft::$app->sites->currentSite->id];
		return SettingsRecord::findOne($params);
	}

	public function renderAgeGate(): bool {
		// $settings = $this->getCurrentSiteAgeGateSettings();

		// DISPLAY ONLY ON FRONTEND
		if( !Craft::$app->request->getIsSiteRequest() ) {
			return false;
		}

		if ( !isset($_COOKIE[self::$settings->cookieName]) && empty($_COOKIE[self::$settings->cookieName]) ) {
			$html = Craft::$app->view->renderTemplate('_agegate/index.twig', ['settings' => self::$settings], View::TEMPLATE_MODE_SITE);
			echo $html;
		}

		return true;
	}
}
