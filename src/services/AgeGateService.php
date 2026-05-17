<?php

namespace thekitchenagency\craftagegate\services;

use Craft;
use craft\base\Component;
use craft\helpers\UrlHelper;
use craft\web\View;
use thekitchenagency\craftagegate\records\SettingsRecord;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;

class AgeGateService extends Component
{
    public static $settings = null;

    /**
     * Get Plugin Settings
     * @return SettingsRecord
     */
    public function getCurrentSiteAgeGateSettings(): SettingsRecord
    {
        try {
            $params = ['agegateSiteId' => Craft::$app->sites->currentSite->id];
            $record = SettingsRecord::findOne($params);
            if ($record) {
                return $record;
            } else {
                // Create new record with site ID
                $record = new SettingsRecord();
                $record->agegateSiteId = Craft::$app->sites->currentSite->id;
                return $record;
            }
        } catch (\Exception $e) {
            // Table doesn't exist yet, return default settings
            $record = new SettingsRecord();
            $record->agegateSiteId = Craft::$app->sites->currentSite->id;
            return $record;
        }
    }

    /**
     * Get all age gate data for custom pages
     * @return array
     */
    public function getAgeGateDataForCustomPages(): array
    {
        $settings = $this->getCurrentSiteAgeGateSettings();
        $originalUrl = Craft::$app->getSession()->get('originalSrcUrl');
        
        // Get privacy policy URL
        $privacyPolicyUrl = null;
        if ($settings->pagePrivacyPolicy) {
            $entry = Craft::$app->getEntries()->getEntryById(json_decode($settings->pagePrivacyPolicy)[0]);
            if ($entry) {
                $privacyPolicyUrl = $entry->url;
            }
        }
        
        // Get cookie policy URL
        $cookiePolicyUrl = null;
        if ($settings->pageCookiePolicy) {
            $entry = Craft::$app->getEntries()->getEntryById(json_decode($settings->pageCookiePolicy)[0]);
            if ($entry) {
                $cookiePolicyUrl = $entry->url;
            }
        }
        
        // Check if age is already verified
        $isAgeVerified = !empty(Craft::$app->getRequest()->getCookies()->get($settings->cookieName));
        
        return [
            'settings' => $settings,
            'originalUrl' => $originalUrl,
            'isAgeVerified' => $isAgeVerified,
            'privacyPolicyUrl' => $privacyPolicyUrl,
            'cookiePolicyUrl' => $cookiePolicyUrl,
            'minAge' => $settings->minimumAgeAllowed,
            'cookieName' => $settings->cookieName,
            'declineUrl' => $settings->declineUrl,
            'title' => $settings->agegateTitle,
            'content' => $settings->agegateContent,
            'agreeButton' => $settings->agegateAgreeButton,
            'declineButton' => $settings->agegateDeclineButton,
            'displayType' => $settings->displayType,
            'isEnabled' => $settings->isAgeGateEnabled,
            'formattedTitle' => str_replace('[minAge]', $settings->minimumAgeAllowed, $settings->agegateTitle),
            'formattedContent' => $this->getFormattedContent($settings),
            'formattedAgreeButton' => str_replace('[minAge]', $settings->minimumAgeAllowed, $settings->agegateAgreeButton),
            'formattedDeclineButton' => str_replace('[minAge]', $settings->minimumAgeAllowed, $settings->agegateDeclineButton),
        ];
    }

    /**
     * Get formatted content with placeholders replaced
     * @param SettingsRecord $settings
     * @return string
     */
    private function getFormattedContent($settings): string
    {
        $content = $settings->agegateContent;
        
        // Replace privacy policy link
        if ($settings->pagePrivacyPolicy) {
            $entry = Craft::$app->getEntries()->getEntryById(json_decode($settings->pagePrivacyPolicy)[0]);
            if ($entry) {
                $content = str_replace('[privacy_page]', '<a href="' . $entry->url . '">Privacy Policy</a>', $content);
            }
        }
        
        // Replace cookie policy link
        if ($settings->pageCookiePolicy) {
            $entry = Craft::$app->getEntries()->getEntryById(json_decode($settings->pageCookiePolicy)[0]);
            if ($entry) {
                $content = str_replace('[cookie_page]', '<a href="' . $entry->url . '">Cookie Policy</a>', $content);
            }
        }
        
        return $content;
    }

    /**
     * Check if current page should be excluded from age gate
     * @return bool
     */
    public function isCurrentPageExcluded(): bool
    {
        $settings = $this->getCurrentSiteAgeGateSettings();
        $entry = [];
        
        if ($settings->pagePrivacyPolicy) {
            $entry[] = Craft::$app->getEntries()->getEntryById(json_decode($settings->pagePrivacyPolicy)[0]);
        }

        if ($settings->pageCookiePolicy) {
            $entry[] = Craft::$app->getEntries()->getEntryById(json_decode($settings->pageCookiePolicy)[0]);
        }

        if ($settings->excludedFromAgeGate) {
            $entry[] = Craft::$app->getEntries()->getEntryById(json_decode($settings->excludedFromAgeGate)[0]);
        }

        $matchingSite = false;
        if ($entry) {
            foreach ($entry as $singleEntry) {
                if ($singleEntry && $singleEntry->slug) {
                    // Check if current URL matches the excluded page slug
                    if (Craft::$app->getRequest()->getSegment(1) === $singleEntry->slug) {
                        $matchingSite = true;
                    }
                    // Also check the full URL path for more robust matching
                    $currentPath = trim(Craft::$app->getRequest()->getPathInfo(), '/');
                    $excludedPath = trim($singleEntry->uri, '/');
                    if ($currentPath === $excludedPath) {
                        $matchingSite = true;
                    }
                }
            }
        }
        
        return $matchingSite;
    }

    /**
     * Render age gate template with custom template override support
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function renderAgeGateTemplate(): string
    {
        // Check for custom template in site templates directory
        $customTemplatePath = 'age-gate/index.twig';
        $pluginTemplatePath = '_agegate/index.twig';
        
        // Try to render custom template first
        try {
            if (Craft::$app->view->doesTemplateExist($customTemplatePath, View::TEMPLATE_MODE_SITE)) {
                return Craft::$app->view->renderTemplate($customTemplatePath, ['settings' => self::$settings], View::TEMPLATE_MODE_SITE);
            }
        } catch (Exception $e) {
            // Custom template exists but has errors, log and fallback
            Craft::getLogger()->log('Custom age gate template has errors: ' . $e->getMessage(), \yii\log\Logger::LEVEL_WARNING, 'craft-age-gate');
        }
        
        // Fallback to plugin template
        return Craft::$app->view->renderTemplate($pluginTemplatePath, ['settings' => self::$settings], View::TEMPLATE_MODE_SITE);
    }

    /**
     * Initialize Redirect Age Gate
     * @return void
     */
    public function initRedirectionAgegate(): void
    {
        if (Craft::$app->request->getIsSiteRequest()) {
            self::$settings = $this->getCurrentSiteAgeGateSettings();

            $entry = [];
            if (self::$settings->pagePrivacyPolicy) {
                $entry[] = Craft::$app->getEntries()->getEntryById(json_decode(self::$settings->pagePrivacyPolicy)[0]);
            }

            if (self::$settings->pageCookiePolicy) {
                $entry[] = Craft::$app->getEntries()->getEntryById(json_decode(self::$settings->pageCookiePolicy)[0]);
            }

            if (self::$settings->excludedFromAgeGate) {
                $entry[] = Craft::$app->getEntries()->getEntryById(json_decode(self::$settings->excludedFromAgeGate)[0]);
            }

            $matchingSite = false;
            if ($entry) {
                foreach ($entry as $singleEntry) {
                    if (Craft::$app->getRequest()->getSegment(1) === $singleEntry->slug) {
                        $matchingSite = true;
                    }
                }
            }

            if (self::$settings->isAgeGateEnabled && !$matchingSite && self::$settings->displayType === 'redirect') {
                // Get the redirect page URL
                $redirectUrl = 'agegate'; // Default fallback
                if (self::$settings->pageRedirection) {
                    $redirectEntry = Craft::$app->getEntries()->getEntryById(json_decode(self::$settings->pageRedirection)[0]);
                    if ($redirectEntry) {
                        $redirectUrl = $redirectEntry->uri;
                    }
                }

                if (self::$settings->isAgeGateEnabled && !$matchingSite && self::$settings->displayType === 'redirect' && Craft::$app->getRequest()->getSegment(1) != trim($redirectUrl, '/')) {
                    $originalUrl = Craft::$app->getRequest()->getFullUri();
                    Craft::$app->getSession()->set('originalSrcUrl', $originalUrl);

                    if (!isset($_COOKIE[self::$settings->cookieName]) || empty($_COOKIE[self::$settings->cookieName])) {
                        Craft::$app->getResponse()->redirect(UrlHelper::siteUrl($redirectUrl))->send();
                    }
                } elseif (self::$settings->isAgeGateEnabled && !$matchingSite && self::$settings->displayType === 'redirect' && Craft::$app->getRequest()->getSegment(1) == trim($redirectUrl, '/')) {
                    Craft::$app->getView()->registerJsVar('originalSrcUrl', Craft::$app->getSession()->get('originalSrcUrl'));

                    if (isset($_COOKIE[self::$settings->cookieName]) || !empty($_COOKIE[self::$settings->cookieName])) {
                        Craft::$app->getResponse()->redirect(Craft::$app->getSession()->get('originalSrcUrl'))->send();
                    }
                } else {
                    Craft::$app->getResponse()->redirect(Craft::$app->getSession()->get('originalSrcUrl'))->send();
                }
            }
        }
    }

    /**
     * Initialize Modal Age Gate
     * @return bool
     */
    public function renderAgeGate(): bool
    {
        self::$settings = $this->getCurrentSiteAgeGateSettings();

        // DISPLAY ONLY ON FRONTEND
        if (!Craft::$app->request->getIsSiteRequest()) {
            return false;
        }

        // Check for excluded pages (same logic as redirect version)
        $entry = [];
        if (self::$settings->pagePrivacyPolicy) {
            $entry[] = Craft::$app->getEntries()->getEntryById(json_decode(self::$settings->pagePrivacyPolicy)[0]);
        }

        if (self::$settings->pageCookiePolicy) {
            $entry[] = Craft::$app->getEntries()->getEntryById(json_decode(self::$settings->pageCookiePolicy)[0]);
        }

        if (self::$settings->excludedFromAgeGate) {
            $entry[] = Craft::$app->getEntries()->getEntryById(json_decode(self::$settings->excludedFromAgeGate)[0]);
        }

        $matchingSite = false;
        if ($entry) {
            foreach ($entry as $singleEntry) {
                if ($singleEntry && $singleEntry->slug) {
                    // Check if current URL matches the excluded page slug
                    if (Craft::$app->getRequest()->getSegment(1) === $singleEntry->slug) {
                        $matchingSite = true;
                    }
                    // Also check the full URL path for more robust matching
                    $currentPath = trim(Craft::$app->getRequest()->getPathInfo(), '/');
                    $excludedPath = trim($singleEntry->uri, '/');
                    if ($currentPath === $excludedPath) {
                        $matchingSite = true;
                    }
                }
            }
        }

        // Don't show modal if current page is excluded
        if ($matchingSite) {
            return false;
        }

        if (!isset($_COOKIE[self::$settings->cookieName]) && empty($_COOKIE[self::$settings->cookieName])) {
            try {
                // Try to render custom template first, fallback to plugin template
                $agegateHTML = $this->renderAgeGateTemplate();
                echo $agegateHTML;
            } catch (LoaderError $e) {
            } catch (RuntimeError $e) {
            } catch (SyntaxError $e) {
            } catch (Exception $e) {
            }
        }

        return true;
    }
}
