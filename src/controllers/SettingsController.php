<?php

namespace thekitchenagency\craftagegate\controllers;

use craft\errors\MissingComponentException;
use thekitchenagency\craftagegate\AgeGate;
use thekitchenagency\craftagegate\records\SettingsRecord;
use thekitchenagency\craftagegate\services\AgeGateService;

use Craft;
use craft\web\Controller;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class SettingsController extends Controller
{
    public function actionIndex(int $siteId = 0)
    {
        if ($siteId == 0) {
            $settingsRecord = SettingsRecord::findOne(1);
            $siteId = Craft::$app->sites->primarySite->id;
        } else {
            $params = ['agegateSiteId' => $siteId];
            $settingsRecord = SettingsRecord::findOne($params);
        }

        if (!$settingsRecord) {
            $settingsRecord = new SettingsRecord();
        }

        if ($settingsRecord) {
            $redirectedPage = [];
            $excludedFromAgeGate = [];

            if ($settingsRecord->pageRedirection) {
                foreach (json_decode($settingsRecord->pageRedirection) as $entryID) {
                    $redirectedPage[] = Craft::$app->elements->getElementById(intval($entryID));
                }
            }

            if ($settingsRecord->excludedFromAgeGate) {
                foreach (json_decode($settingsRecord->excludedFromAgeGate) as $entryID) {
                    $excludedFromAgeGate[] = Craft::$app->elements->getElementById(intval($entryID));
                }
            }
        }

        return $this->renderTemplate('craft-age-gate/settings', [
            'settings' => $settingsRecord,
            'siteId' => $siteId,
            'pageRedirected' => $redirectedPage,
            'excludedFromAgeGate' => $excludedFromAgeGate,
        ]);
    }

    /**
     * @throws MissingComponentException
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function actionSavePluginSettings()
    {
        $this->requirePostRequest();

        $settings = Craft::$app->getRequest()->getBodyParam('settings', []);
        $plugin = Craft::$app->getPlugins()->getPlugin('craft-age-gate');

        if ($plugin === null) {
            throw new NotFoundHttpException('Plugin not found');
        }

        $params = ['agegateSiteId' => $settings['siteId']];

        $longAccessRecord = SettingsRecord::findOne($params);

        if (!$longAccessRecord) {
            $longAccessRecord = new SettingsRecord();
        }

        $longAccessRecord->setAttribute('agegateSiteId', $settings['siteId']);
        $longAccessRecord->setAttribute('isAgeGateEnabled', $settings['isAgeGateEnabled']);
        $longAccessRecord->setAttribute('displayType', $settings['displayType']);
        $longAccessRecord->setAttribute('validationType', $settings['validationType'] ?? 'simple');
        $longAccessRecord->setAttribute('validationInputStyle', $settings['validationInputStyle'] ?? 'single');
        $longAccessRecord->setAttribute('birthdateFormat', $settings['birthdateFormat'] ?? 'dmy');
        $longAccessRecord->setAttribute('primaryColor', $settings['primaryColor'] ?? '#ff6b6b');
        $longAccessRecord->setAttribute('backgroundColor', $settings['backgroundColor'] ?? 'rgba(255, 255, 255, 0.25)');
        $longAccessRecord->setAttribute('blurAmount', $settings['blurAmount'] ?? '16px');
        $longAccessRecord->setAttribute('borderRadius', $settings['borderRadius'] ?? '24px');
        $longAccessRecord->setAttribute('minimumAgeAllowed', $settings['minimumAgeAllowed']);
        $longAccessRecord->setAttribute('cookieName', $settings['cookieName']);
        $longAccessRecord->setAttribute('declineUrl', $settings['declineUrl']);
        $longAccessRecord->setAttribute('agegateTitle', $settings['agegateTitle']);
        $longAccessRecord->setAttribute('agegateContent', $settings['agegateContent']);
        $longAccessRecord->setAttribute('agegateAgreeButton', $settings['agegateAgreeButton']);
        $longAccessRecord->setAttribute('agegateDeclineButton', $settings['agegateDeclineButton']);
        $longAccessRecord->setAttribute('pageRedirection', $settings['pageRedirection']);
        $longAccessRecord->setAttribute('excludedFromAgeGate', $settings['excludedFromAgeGate']);

        if (!$longAccessRecord->save()) {
            Craft::$app->getSession()->setError(Craft::t('app', "cant save settings"));

            Craft::$app->getUrlManager()->setRouteParams([
                'plugin' => $plugin
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('app', 'Plugin settings saved.'));
        return $this->redirectToPostedUrl();
    }
}
