<?php

namespace thekitchenagency\craftagegate;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterTemplateRootsEvent;
use craft\helpers\UrlHelper;
use craft\web\twig\variables\CraftVariable;
use craft\web\View;
use thekitchenagency\craftagegate\models\Settings;
use thekitchenagency\craftagegate\services\AgeGateService;
use thekitchenagency\craftagegate\resources\AgeGateAssets;
use thekitchenagency\craftagegate\variables\AgeGateVariable;
use yii\base\Event;

/**
 * Agegate plugin
 *
 * @method static AgeGate getInstance()
 * @method Settings getSettings()
 * @author thekitchen.agency
 * @copyright thekitchen.agency
 * @license https://craftcms.github.io/license/ Craft License
 */
class AgeGate extends Plugin {
	public static $plugin;
	public static $settings;

	public string $schemaVersion = '1.0.2';
	public bool $hasCpSettings = true;

	public static function config(): array {
		return [
			'components' => [
				'ageGate' => AgeGateService::class,
			],
		];
	}

	public function init() {
		parent::init();
		// $this->attachEventHandlers();

		if ( Craft::$app->getRequest()->getIsSiteRequest() ) {
			Craft::$app->getView()->registerAssetBundle( AgeGateAssets::class );
			Craft::$app->getView()->registerJsVar( 'agegatesettings', $this->getSettings() );

		}

		Craft::$app->onInit( function () {
			$this->attachEventHandlers();
			self::$plugin   = $this;
			self::$settings = $this->getSettings();


			$entry = [];
			if(self::$settings->pagePrivacyPolicy) {
				$entry[] = Craft::$app->getEntries()->getEntryById(self::$settings->pagePrivacyPolicy[0]);
			}

			if(self::$settings->pageCookiePolicy) {
				$entry[] = Craft::$app->getEntries()->getEntryById(self::$settings->pageCookiePolicy[0]);
			}

			$matchingSite = false;
			if($entry) {
				foreach ( $entry as $singleEntry ) {
					if ( Craft::$app->getRequest()->getSegment(1) === $singleEntry->slug ) {
						$matchingSite = true;
					}
				}
			}

			if ( self::$settings->isAgeGateEnabled && !$matchingSite && self::$settings->displayType === 'modal' ) {
				$this->ageGate->renderAgeGate();
			} else if( self::$settings->isAgeGateEnabled && !$matchingSite && self::$settings->displayType === 'redirect' && Craft::$app->getResponse().getSegment(1) != 'agegate' ) {
				Craft::$app->getResponse()->redirect(UrlHelper::siteUrl('agegate'))->send();
			}
		} );

	}

	protected function createSettingsModel(): ?Model {
		return Craft::createObject( Settings::class );
	}

	protected function settingsHtml(): ?string {
		$pagePrivacy = [];
		$pageCookie  = [];
		$redirectedPage = [];

		if ( self::$settings->pagePrivacyPolicy ) {
			foreach ( self::$settings->pagePrivacyPolicy as $entryID ) {
				$pagePrivacy[] = Craft::$app->elements->getElementById( $entryID );
			}
		}

		if ( self::$settings->pageCookiePolicy ) {
			foreach ( self::$settings->pageCookiePolicy as $entryID ) {
				$pageCookie[] = Craft::$app->elements->getElementById( $entryID );
			}
		}

		if ( self::$settings->pageRedirection ) {
			foreach ( self::$settings->pageCookiePolicy as $entryID ) {
				$redirectedPage[] = Craft::$app->elements->getElementById( $entryID );
			}
		}

		return Craft::$app->view->renderTemplate( 'craft-agegate/_settings.twig', [
			'plugin'      => $this,
			'pagePrivacy' => $pagePrivacy,
			'pageCookie'  => $pageCookie,
			'pageRedirected' => $redirectedPage,
			'settings'    => $this->getSettings(),
		] );
	}

	private function attachEventHandlers(): void {
		Event::on(
			View::class,
			View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS,
			function ( RegisterTemplateRootsEvent $event ) {
				$event->roots['_agegate'] = __DIR__ . '/templates/agegate/';
			}
		);

		Event::on(
			CraftVariable::class,
			CraftVariable::EVENT_INIT,
			function (Event $event) {
				/** @var CraftVariable $variable */
				$variable = $event->sender;
				$variable->set('agegate', AgeGateVariable::class);
			}
		);
	}
}