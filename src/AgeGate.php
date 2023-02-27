<?php

namespace thekitchenagency\craftagegate;

use Craft;
use craft\elements\Entry;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\web\UrlManager;
use thekitchenagency\craftagegate\models\Settings;
use thekitchenagency\craftagegate\services\AgeGateService;
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
class AgeGate extends Plugin
{
	public static $plugin;
	public static $settings;

    public string $schemaVersion = '1.0.2';
    public bool $hasCpSettings = true;

    public static function config(): array
    {
        return [
            'components' => [
                // Define component configs here...
            ],
        ];
    }

    public function init()
    {
        parent::init();

        Craft::$app->onInit(function() {
            $this->attachEventHandlers();

	        self::$plugin = $this;

	        self::$settings = $this->getSettings();

	        Craft::setAlias('@agegate', $this->getBasePath());

	        /*$this->setComponents([
		        'agegate' => AgeGateService::class,
	        ]);*/

	        /*Craft::$app->view->hook('cp.entries.edit.details', function(array &$context) {
		        return '<p>Hello there.</p>';
	        });*/

			// Craft::$app->getView()->registerTwigExtension(new \thekitchenagency\craftagegate\twigextensions\AgeGateTwigExtension());
        });
    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('craft-agegate/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
        ]);
    }

    private function attachEventHandlers(): void
    {

    }
}
