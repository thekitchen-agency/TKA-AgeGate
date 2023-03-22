<?php

namespace thekitchenagency\craftagegate\services;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;
use craft\web\View;
use thekitchenagency\craftagegate\records\SettingsRecord;

class AgeGateService extends Component {
	public static $settings = null;

	/**
	 * Get Plugin Settings
	 * @return SettingsRecord
	 */
	public function getCurrentSiteAgeGateSettings(): SettingsRecord {
		$params = [ 'agegateSiteId' => Craft::$app->sites->currentSite->id ];

		return SettingsRecord::findOne( $params );
	}

	/**
	 * Initialize Redirect Age Gate
	 * @return void
	 */
	public function initRedirectionAgegate(): void {
		if ( Craft::$app->request->getIsSiteRequest() ) {
			self::$settings = $this->getCurrentSiteAgeGateSettings();

			$entry = [];
			if ( self::$settings->pagePrivacyPolicy ) {
				$entry[] = Craft::$app->getEntries()->getEntryById( json_decode( self::$settings->pagePrivacyPolicy )[0] );
			}

			if ( self::$settings->pageCookiePolicy ) {
				$entry[] = Craft::$app->getEntries()->getEntryById( json_decode( self::$settings->pageCookiePolicy )[0] );
			}

			$matchingSite = false;
			if ( $entry ) {
				foreach ( $entry as $singleEntry ) {
					if ( Craft::$app->getRequest()->getSegment( 1 ) === $singleEntry->slug ) {
						$matchingSite = true;
					}
				}
			}

			if ( self::$settings->isAgeGateEnabled && ! $matchingSite && self::$settings->displayType === 'redirect' ) {
				if ( Craft::$app->request->getIsSiteRequest() ) {
					if ( self::$settings->isAgeGateEnabled && ! $matchingSite && self::$settings->displayType === 'redirect' && Craft::$app->getRequest()->getSegment( 1 ) != 'agegate' ) {
						$originalUrl = Craft::$app->getRequest()->getFullUri();
						Craft::$app->getSession()->set( 'originalSrcUrl', $originalUrl );

						if ( ! isset( $_COOKIE[ self::$settings->cookieName ] ) || empty( $_COOKIE[ self::$settings->cookieName ] ) ) {
							Craft::$app->getResponse()->redirect( UrlHelper::siteUrl( 'agegate' ) )->send();
						}

					} else if ( self::$settings->isAgeGateEnabled && ! $matchingSite && self::$settings->displayType === 'redirect' && Craft::$app->getRequest()->getSegment( 1 ) == 'agegate' ) {
						Craft::$app->getView()->registerJsVar( 'originalSrcUrl', Craft::$app->getSession()->get( 'originalSrcUrl' ) );

						if ( isset( $_COOKIE[ self::$settings->cookieName ] ) || ! empty( $_COOKIE[ self::$settings->cookieName ] ) ) {
							Craft::$app->getResponse()->redirect( Craft::$app->getSession()->get( 'originalSrcUrl' ) )->send();
						}
					} else {
						Craft::$app->getResponse()->redirect( Craft::$app->getSession()->get( 'originalSrcUrl' ) )->send();
					}
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
		if ( ! Craft::$app->request->getIsSiteRequest() ) {
			return false;
		}

		if ( ! isset( $_COOKIE[ self::$settings->cookieName ] ) && empty( $_COOKIE[ self::$settings->cookieName ] ) ) {
			$html = Craft::$app->view->renderTemplate( '_agegate/index.twig', [ 'settings' => self::$settings ], View::TEMPLATE_MODE_SITE );
			echo $html;
		}

		return true;
	}
}
