<?php

namespace thekitchenagency\craftagegate;

use AWS\CRT\Log;
use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\helpers\UrlHelper;
use craft\log\MonologTarget;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use craft\web\View;
use Monolog\Formatter\LineFormatter;
use Psr\Log\LogLevel;
use thekitchenagency\craftagegate\models\Settings;
use thekitchenagency\craftagegate\resources\AgeGateAssets;
use thekitchenagency\craftagegate\services\AgeGateService as AgeGateService;
use thekitchenagency\craftagegate\variables\AgeGateVariable;
// use thekitchenagency\craftagegate\resources\AgeGateAssets;
use yii\base\Event;
use yii\log\Logger;

/**
 * Agegate plugin
 *
 * @author thekitchen.agency
 * @copyright thekitchen.agency
 * @license https://craftcms.github.io/license/ Craft License
 */
class AgeGate extends Plugin {
	// Static Properties
	// =========================================================================
	/**
	 * @var AgeGate
	 */
	public static $plugin;

	// Public Properties
	// =========================================================================

	public string $schemaVersion = '1.0.3';
	public bool $hasCpSettings = true;
	public bool $hasCpSection = true;

	// Public Methods
	// =========================================================================

	/**
	 * @inheritdoc
	 */
	public function init() {
		parent::init();
		self::$plugin = $this;

		if ( Craft::$app->getRequest()->getIsSiteRequest() ) {
			Craft::$app->getView()->registerAssetBundle( AgeGateAssets::class );
			Craft::$app->getView()->registerJsVar( 'agegatesettings', $this->getSettings() );

			/*$this->setComponents([
				'ageGateService' => AgeGateService::class,
			]);*/
		}
		$this->attachEventHandlers();

		Craft::$app->onInit( function () {
			/*self::$settings = $this->getSettings();
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
			}*/



			/*if(Craft::$app->request->getIsSiteRequest()) {
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
			}*/
		} );
	}

	/**
	 * Logs a message to the plugin's log file.
	 *
	 * @param string $message
	 * @param string $type
	 *
	 * @return void
	 */
	public function log(string $message, string $type = Logger::LEVEL_INFO): void {
		Craft::getLogger()->log($message, $type, 'craft-agegate');
	}

	public function afterSaveSettings(): void {
		parent::afterSaveSettings();
		Craft::$app->response
			->redirect(UrlHelper::cpUrl('craft-agegate/settings'))
			->send();
	}

	public function getSettingsResponse(): mixed {
		return Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('craft-agegate/settings'));
	}

	// Protected Methods
	// =========================================================================

	/**
	 * @inheritdoc
	 */
	protected function createSettingsModel(): ?Model {
		return new Settings();
	}

	protected function settingsHtml(): ?string {
		return Craft::$app->view->renderTemplate('craft-agegate/settings');
	}

	private function attachEventHandlers(): void {
		Event::on(
			UrlManager::class,
			UrlManager::EVENT_REGISTER_CP_URL_RULES,
			function(RegisterUrlRulesEvent $event) {
				$event->rules = array_merge($event->rules, [
					'craft-agegate/settings' => 'craft-agegate/settings/index',
					'craft-agegate/settings/<siteId>' => 'craft-agegate/settings/index',
				]);
			}
		);

		Event::on(
			Plugins::class,
			Plugins::EVENT_AFTER_INSTALL_PLUGIN,
			function (PluginEvent $event) {
				if ($event->plugin === $this) {
					$request = Craft::$app->getRequest();
					if ($request->isCpRequest) {
						Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('craft-agegate/settings'))->send();
					}
				}
			}
		);

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

		Craft::info(
			Craft::t(
				'craft-agegate',
				'{name} plugin loaded',
				['name' => $this->name]
			),
			__METHOD__
		);

		$this->_registerLogTarget();
	}

	private function _registerLogTarget() {
		Craft::getLogger()->dispatcher->targets[] = new MonologTarget([
			'name' => $this->name,
			'categories' => [$this->name],
			'levels' => [LogLevel::INFO],
			'logContext' => false,
			'allowLineBreaks' => false,
			'formatter' => new LineFormatter(
				format: "[%datetime%] %message%\n",
				dateFormat: 'Y-m-d H:i:s',
			),
		]);
	}
}
