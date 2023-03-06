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

	/**
	 * Get Plugin Settings
	 * @return SettingsRecord
	 */
	public function getCurrentSiteAgeGateSettings(): SettingsRecord {
		$params = ['agegateSiteId' => Craft::$app->sites->currentSite->id];
		return SettingsRecord::findOne($params);
	}

	/**
	 * Initialize Redirect Age Gate
	 * @return void
	 */
	public function initRedirectionAgegate(): void {
		self::$settings = $this->getCurrentSiteAgeGateSettings();

		$matchingSite = false;
		if(self::$settings->isAgeGateEnabled && ! $matchingSite  && self::$settings->displayType === 'redirect') {
			if ( Craft::$app->request->getIsSiteRequest() ) {
				if ( self::$settings->isAgeGateEnabled && !$matchingSite && self::$settings->displayType === 'redirect' && Craft::$app->getRequest()->getSegment( 1 ) != 'agegate' ) {
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

	/**
	 * Initialize Modal Age Gate
	 * @return bool
	 */
	public function renderAgeGate(): bool {
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
