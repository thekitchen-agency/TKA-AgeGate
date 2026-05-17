<?php

namespace thekitchenagency\craftagegate\variables;

use Craft;
use thekitchenagency\craftagegate\AgeGate;

class AgeGateVariable
{
    public function renderAGElement()
    {
        return AgeGate::$plugin->ageGateService->renderAgeGate();
    }

    /**
     * Get all plugin settings
     * @return \thekitchenagency\craftagegate\records\SettingsRecord
     */
    public function getSettings()
    {
        return AgeGate::$plugin->ageGateService->getCurrentSiteAgeGateSettings();
    }

    /**
     * Get the original URL before redirect
     * @return string|null
     */
    public function getOriginalUrl()
    {
        return Craft::$app->getSession()->get('originalSrcUrl');
    }

    /**
     * Check if age has been verified (cookie exists)
     * @return bool
     */
    public function isAgeVerified()
    {
        $settings = $this->getSettings();
        return !empty(Craft::$app->getRequest()->getCookies()->get($settings->cookieName));
    }

    /**
     * Get privacy policy page URL
     * @return string|null
     */
    public function getPrivacyPolicyUrl()
    {
        $settings = $this->getSettings();
        if ($settings->pagePrivacyPolicy) {
            $entry = Craft::$app->getEntries()->getEntryById(json_decode($settings->pagePrivacyPolicy)[0]);
            return $entry ? $entry->url : null;
        }
        return null;
    }

    /**
     * Get cookie policy page URL
     * @return string|null
     */
    public function getCookiePolicyUrl()
    {
        $settings = $this->getSettings();
        if ($settings->pageCookiePolicy) {
            $entry = Craft::$app->getEntries()->getEntryById(json_decode($settings->pageCookiePolicy)[0]);
            return $entry ? $entry->url : null;
        }
        return null;
    }

    /**
     * Get minimum age allowed
     * @return int
     */
    public function getMinAge()
    {
        return $this->getSettings()->minimumAgeAllowed;
    }

    /**
     * Get cookie name
     * @return string
     */
    public function getCookieName()
    {
        return $this->getSettings()->cookieName;
    }

    /**
     * Get decline URL
     * @return string
     */
    public function getDeclineUrl()
    {
        return $this->getSettings()->declineUrl;
    }

    /**
     * Get all age gate data in one array for easy access
     * @return array
     */
    public function getAgeGateData()
    {
        $settings = $this->getSettings();
        return [
            'settings' => $settings,
            'originalUrl' => $this->getOriginalUrl(),
            'isVerified' => $this->isAgeVerified(),
            'privacyPolicyUrl' => $this->getPrivacyPolicyUrl(),
            'cookiePolicyUrl' => $this->getCookiePolicyUrl(),
            'minAge' => $this->getMinAge(),
            'cookieName' => $this->getCookieName(),
            'declineUrl' => $this->getDeclineUrl(),
            'title' => $settings->agegateTitle,
            'content' => $settings->agegateContent,
            'agreeButton' => $settings->agegateAgreeButton,
            'declineButton' => $settings->agegateDeclineButton,
            'displayType' => $settings->displayType,
            'isEnabled' => $settings->isAgeGateEnabled,
        ];
    }

    /**
     * Get formatted title with age placeholder replaced
     * @return string
     */
    public function getFormattedTitle()
    {
        $settings = $this->getSettings();
        return str_replace('[minAge]', $this->getMinAge(), $settings->agegateTitle);
    }

    /**
     * Get formatted content with placeholders replaced
     * @return string
     */
    public function getFormattedContent()
    {
        $settings = $this->getSettings();
        $content = $settings->agegateContent;
        
        // Replace privacy policy link
        if ($privacyUrl = $this->getPrivacyPolicyUrl()) {
            $content = str_replace('[privacy_page]', '<a href="' . $privacyUrl . '">Privacy Policy</a>', $content);
        }
        
        // Replace cookie policy link
        if ($cookieUrl = $this->getCookiePolicyUrl()) {
            $content = str_replace('[cookie_page]', '<a href="' . $cookieUrl . '">Cookie Policy</a>', $content);
        }
        
        return $content;
    }

    /**
     * Get formatted agree button text with age placeholder replaced
     * @return string
     */
    public function getFormattedAgreeButton()
    {
        $settings = $this->getSettings();
        return str_replace('[minAge]', $this->getMinAge(), $settings->agegateAgreeButton);
    }

    /**
     * Get formatted decline button text with age placeholder replaced
     * @return string
     */
    public function getFormattedDeclineButton()
    {
        $settings = $this->getSettings();
        return str_replace('[minAge]', $this->getMinAge(), $settings->agegateDeclineButton);
    }

    /**
     * Check if custom age gate template exists
     * @return bool
     */
    public function hasCustomTemplate()
    {
        return \Craft::$app->view->doesTemplateExist('age-gate/index.twig', \craft\web\View::TEMPLATE_MODE_SITE);
    }

    /**
     * Get the template path that will be used (custom or plugin)
     * @return string
     */
    public function getTemplatePath()
    {
        if ($this->hasCustomTemplate()) {
            return 'age-gate/index.twig';
        }
        return '_agegate/index.twig';
    }
}
