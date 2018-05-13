<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\services;

use yii\base\Component;
use craft\helpers\Json;
use Craft;

class Settings extends Component
{
    /**
     * Save the plugin settings to the database
     *
     * @param $settings
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    public function saveSettings($settings)
    {
        $plugin = Craft::$app->plugins->getPlugin('sprout-seo');
        $seoSettings = $plugin->getSettings();

        if (isset($settings['pluginNameOverride'])) {
            $seoSettings->pluginNameOverride = $settings['pluginNameOverride'] != null ?
                $settings['pluginNameOverride'] :
                $seoSettings->pluginNameOverride;
        }

        if (isset($settings['seoDivider'])) {
            $seoSettings->seoDivider = $settings['seoDivider'] != null ?
                $settings['seoDivider'] :
                $seoSettings->seoDivider;
        }

        if (isset($settings['twitterCard'])) {
            $seoSettings->twitterCard = $settings['twitterCard'] != null ?
                $settings['twitterCard'] :
                $seoSettings->twitterCard;
        }

        if (isset($settings['ogType'])) {
            $seoSettings->ogType = $settings['ogType'] != null ?
                $settings['ogType'] :
                $seoSettings->ogType;
        }

        if (isset($settings['enableDynamicSitemaps'])) {
            $seoSettings->enableDynamicSitemaps = $settings['enableDynamicSitemaps'] != null ?
                $settings['enableDynamicSitemaps'] :
                $seoSettings->enableDynamicSitemaps;
        }

        if (isset($settings['totalElementsPerSitemap'])) {
            $seoSettings->totalElementsPerSitemap = $settings['totalElementsPerSitemap'] != null ?
                $settings['totalElementsPerSitemap'] :
                $seoSettings->totalElementsPerSitemap;
        }

        if (isset($settings['enable404RedirectLog'])) {
            $seoSettings->enable404RedirectLog = $settings['enable404RedirectLog'] != null ?
                $settings['enable404RedirectLog'] :
                $seoSettings->enable404RedirectLog;
        }

        if (isset($settings['total404Redirects'])) {
            $seoSettings->total404Redirects = $settings['total404Redirects'] != null ?
                $settings['total404Redirects'] :
                $seoSettings->total404Redirects;
        }

        $seoSettings->localeIdOverride = $settings['localeIdOverride'] ?? $seoSettings->localeIdOverride;


        $seoSettings->displayFieldHandles = $settings['displayFieldHandles'] ?? $seoSettings->displayFieldHandles;

        $seoSettings->enableMetaDetailsFields = $settings['enableMetaDetailsFields'] ?? $seoSettings->enableMetaDetailsFields;

        $seoSettings->enableCustomSections = $settings['enableCustomSections'] ?? $seoSettings->enableCustomSections;

        $seoSettings->enableMetadataRendering = $settings['enableMetadataRendering'] ?? $seoSettings->enableMetadataRendering;

        if (isset($settings['toggleMetadataVariable']) and isset($settings['metadataVariable'])) {
            if (isset($settings['toggleMetadataVariable']) and $settings['toggleMetadataVariable'] == 0) {
                $seoSettings->metadataVariable = null;
            }

            if (isset($settings['toggleMetadataVariable']) and $settings['toggleMetadataVariable'] == 1) {
                $seoSettings->metadataVariable = $settings['metadataVariable']
                ?? $seoSettings->metadataVariable;
            }
        }

        $seoSettings->enable404RedirectLog = $settings['enable404RedirectLog'] ?? $seoSettings->enable404RedirectLog;

        $settings = Json::encode($seoSettings);

        $affectedRows = Craft::$app->db->createCommand()->update(
            '{{%plugins}}', [
            'settings' => $settings
        ],
            [
                'handle' => 'sprout-seo'
            ])->execute();

        return (bool)$affectedRows;
    }

    public function getPluginPath()
    {
        return Craft::getAlias('@barrelstrength/sproutseo/');
    }

    public function getDescriptionLength()
    {
        $settings = Craft::$app->plugins->getPlugin('sprout-seo')->getSettings();
        $descriptionLength = $settings->maxMetaDescriptionLength;
        $descriptionLength = $descriptionLength > 160 ? $descriptionLength : 160;

        return $descriptionLength;
    }
}
