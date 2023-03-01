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
use yii\log\Logger;
use nystudio107\pluginvite\services\VitePluginService;

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
				'vite' => [
					'class' => VitePluginService::class,
					// 'assetClass' => RetourAsset::class,
					'useDevServer' => true,
					'devServerPublic' => 'http://localhost:3001',
					'serverPublic' => 'http://calanda.localhost',
					//'errorEntry' => 'src/js/Retour.js',
					'devServerInternal' => 'http://calanda.localhost:3001',
					'checkDevServer' => true,
				],

			],
		];
	}

	public function init() {
		parent::init();
		// $this->attachEventHandlers();

		if ( Craft::$app->getRequest()->getIsSiteRequest() ) {
			Craft::$app->getView()->registerAssetBundle( AgeGateAssets::class );
			Craft::$app->getView()->registerJsVar( 'agegatesettings', $this->getSettings() );

			$this->setComponents([
				'ageGateService' => AgeGateService::class,
			]);
		}

		Craft::$app->onInit( function () {
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

			if(Craft::$app->request->getIsSiteRequest()) {
				if ( self::$settings->isAgeGateEnabled && !$matchingSite && self::$settings->displayType === 'modal' ) {
					$this->ageGateService->renderAgeGate();
				} else if( self::$settings->isAgeGateEnabled && !$matchingSite && self::$settings->displayType === 'redirect' && Craft::$app->getRequest()->getSegment(1) != 'agegate' ) {
					$originalUrl = Craft::$app->getRequest()->getFullUri();
					Craft::$app->getSession()->set('originalSrcUrl', $originalUrl);

					if ( !isset($_COOKIE[self::$settings->cookieName]) || empty($_COOKIE[self::$settings->cookieName]) ) {
						Craft::$app->getResponse()->redirect(UrlHelper::siteUrl('agegate'))->send();
					}

				} else if( self::$settings->isAgeGateEnabled && !$matchingSite && self::$settings->displayType === 'redirect' && Craft::$app->getRequest()->getSegment(1) == 'agegate' ) {
					Craft::$app->getView()->registerJsVar( 'originalSrcUrl', Craft::$app->getSession()->get('originalSrcUrl') );
				}
			}
		} );

		$this->attachEventHandlers();
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
			foreach ( self::$settings->pageRedirection as $entryID ) {
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
				$variable->set('ageGate', AgeGateVariable::class);
			}
		);

		Craft::getLogger()->log($this->id . ' loaded successfully', Logger::LEVEL_INFO, $this->id);
	}
}
