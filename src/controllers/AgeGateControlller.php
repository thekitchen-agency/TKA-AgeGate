<?php

namespace thekitchenagency\craftagegate\controllers;

use Craft;
use craft\web\Controller;
use craft\web\View;

class AgeGateController extends Controller {
	public function actionRenderTemplate() {
		/*self::$settings = $this->getSettings();
		$entry          = [];
		if ( self::$settings->pagePrivacyPolicy ) {
			$entry[] = Craft::$app->getEntries()->getEntryById( self::$settings->pagePrivacyPolicy[0] );
		}

		if ( self::$settings->pageCookiePolicy ) {
			$entry[] = Craft::$app->getEntries()->getEntryById( self::$settings->pageCookiePolicy[0] );
		}

		$matchingSite = false;
		if ( $entry ) {
			foreach ( $entry as $singleEntry ) {
				if ( Craft::$app->getRequest()->getSegment( 1 ) === $singleEntry->slug ) {
					$matchingSite = true;
				}
			}
		}


		if ( Craft::$app->request->getIsSiteRequest() ) {
			if ( self::$settings->isAgeGateEnabled && ! $matchingSite && self::$settings->displayType === 'modal' ) {
				$this->ageGateService->renderAgeGate();
			} else if ( self::$settings->isAgeGateEnabled && ! $matchingSite && self::$settings->displayType === 'redirect' && Craft::$app->getRequest()->getSegment( 1 ) != 'agegate' ) {
				$originalUrl = Craft::$app->getRequest()->getFullUri();
				Craft::$app->getSession()->set( 'originalSrcUrl', $originalUrl );

				if ( ! isset( $_COOKIE[ self::$settings->cookieName ] ) || empty( $_COOKIE[ self::$settings->cookieName ] ) ) {
					Craft::$app->getResponse()->redirect( UrlHelper::siteUrl( 'agegate' ) )->send();
				}

			} else if ( self::$settings->isAgeGateEnabled && ! $matchingSite && self::$settings->displayType === 'redirect' && Craft::$app->getRequest()->getSegment( 1 ) == 'agegate' ) {
				Craft::$app->getView()->registerJsVar( 'originalSrcUrl', Craft::$app->getSession()->get( 'originalSrcUrl' ) );
			}
		}*/
	}
}
