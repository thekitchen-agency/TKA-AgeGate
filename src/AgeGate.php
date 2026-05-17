<?php

namespace thekitchenagency\craftagegate;

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
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Monolog\Formatter\LineFormatter;
use Psr\Log\LogLevel;
use thekitchenagency\craftagegate\controllers\SettingsController;
use thekitchenagency\craftagegate\models\Settings;
use thekitchenagency\craftagegate\resources\AgeGateAssets;
use thekitchenagency\craftagegate\services\AgeGateService;
use thekitchenagency\craftagegate\variables\AgeGateVariable;
use yii\base\Event;
use yii\log\Logger;

/**
 * AgeGate plugin
 *
 * @method static AgeGate getInstance()
 * @method Settings getSettings()
 * @author thekitchen.agency <tech@thekitchen.agency>
 * @copyright thekitchen.agency
 * @license https://craftcms.github.io/license/ Craft License
 */
class AgeGate extends Plugin
{
    // Static Properties
    // =========================================================================
    /**
     * @var AgeGate
     */
    public static $plugin;
    public static $settings;

    // Public Properties
    // =========================================================================

    public string $schemaVersion = '1.0.5';
    public bool $hasCpSettings = true;
    public bool $hasCpSection = false;

    public static function config(): array
    {
        return [
            'components' => [
                'ageGateService' => AgeGateService::class,
            ],
        ];
    }

    public function init(): void
    {
        // Skip initialization during console commands to avoid database issues during migration
        if (Craft::$app->getRequest()->isConsoleRequest) {
            parent::init();
            self::$plugin = $this;
            return;
        }

        $crawlerDetect = new CrawlerDetect();

        if ($crawlerDetect->isCrawler()) {
            return;
        }

        parent::init();
        self::$plugin = $this;

        try {
            self::$settings = $this->ageGateService->getCurrentSiteAgeGateSettings();
        } catch (\Exception $e) {
            // Service not available during migration, use default settings
            self::$settings = new \thekitchenagency\craftagegate\models\Settings();
        }

        if (Craft::$app->getRequest()->getIsSiteRequest()) {
            Craft::$app->getView()->registerAssetBundle(AgeGateAssets::class);
            try {
                // Register basic settings
                Craft::$app->getView()->registerJsVar('agegatesettings', $this->ageGateService->getCurrentSiteAgeGateSettings());
                
                // Register enhanced data for custom pages
                Craft::$app->getView()->registerJsVar('ageGateData', $this->ageGateService->getAgeGateDataForCustomPages());
                
                // Register original URL for redirect functionality
                Craft::$app->getView()->registerJsVar('originalSrcUrl', Craft::$app->getSession()->get('originalSrcUrl'));
                
                // Register helper functions availability
                Craft::$app->getView()->registerJsVar('ageGateHelpersAvailable', true);
            } catch (\Exception $e) {
                // Service not available, skip JS var registration
            }
        }
        
        $this->attachEventHandlers();

        Craft::$app->onInit(function () {
            if (self::$settings && self::$settings->isAgeGateEnabled && self::$settings->displayType === 'modal') {
                if (Craft::$app->request->getIsSiteRequest()) {
                    if (!isset($_COOKIE[self::$settings->cookieName]) && empty($_COOKIE[self::$settings->cookieName])) {
                        $this->ageGateService->renderAgeGate();
                    }
                }
            } elseif (self::$settings && self::$settings->isAgeGateEnabled && self::$settings->displayType === 'redirect') {
                $this->ageGateService->initRedirectionAgegate();
            }
        });
    }

    /**
     * Logs a message to the plugin's log file.
     *
     * @param string $message
     * @param string $type
     *
     * @return void
     */
    public function log(string $message, string $type = Logger::LEVEL_INFO): void
    {
        Craft::getLogger()->log($message, $type, 'craft-age-gate');
    }

    public function afterSaveSettings(): void
    {
        parent::afterSaveSettings();
        Craft::$app->response
            ->redirect(UrlHelper::cpUrl('craft-age-gate/settings'))
            ->send();
    }

    public function getSettingsResponse(): mixed
    {
        return Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('craft-age-gate/settings'));
    }

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('craft-age-gate/settings');
    }

    private function attachEventHandlers(): void
    {
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules = array_merge($event->rules, [
                    'craft-age-gate/settings' => 'craft-age-gate/settings/index',
                    'craft-age-gate/settings/<siteId>' => 'craft-age-gate/settings/index',
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
                        Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('craft-age-gate/settings'))->send();
                    }
                }
            }
        );

        Event::on(
            View::class,
            View::EVENT_REGISTER_SITE_TEMPLATE_ROOTS,
            function (RegisterTemplateRootsEvent $event) {
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
                'craft-age-gate',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );

        $this->_registerLogTarget();
    }

    private function _registerLogTarget()
    {
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
