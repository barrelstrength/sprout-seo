<?php

namespace barrelstrength\sproutseo\migrations;

use barrelstrength\sproutbasesitemaps\migrations\Install as SproutBaseSitemapsInstall;
use barrelstrength\sproutbasesitemaps\models\Settings as SproutSitemapSettings;
use barrelstrength\sproutbasesitemaps\SproutBaseSitemaps;
use craft\db\Migration;
use Craft;

/**
 * m190415_000001_adds_sprout_sitemaps_migration migration.
 */
class m190415_000001_adds_sprout_sitemaps_migration extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $migration = new SproutBaseSitemapsInstall();

        ob_start();
        $migration->safeUp();
        ob_end_clean();

        // See if we already have settings
        $settings = $this->getSproutSitemapSettingsModel();

        if (is_array($settings->siteSettings)) {
            // Add our default plugin settings
            SproutBaseSitemaps::$app->sitemaps->saveSitemapsSettings($settings->toArray());
        }

        return true;
    }

    /**
     * @return SproutSitemapSettings
     * @throws \craft\errors\SiteNotFoundException
     */
    private function getSproutSitemapSettingsModel(): SproutSitemapSettings
    {
        $projectConfig = Craft::$app->getProjectConfig();
        $settings = new SproutSitemapSettings();
        $pluginHandle = 'sprout-base-sitemaps';

        // Need to fix how settings were stored in an earlier install
        // @deprecate in future version
        $sproutBaseSitemapSettings = $projectConfig->get('plugins.'.$pluginHandle.'.settings');

        if ($sproutBaseSitemapSettings &&
            isset($sproutBaseSitemapSettings['siteSettings']) &&
            !empty($sproutBaseSitemapSettings['siteSettings'])) {

            $settings->pluginNameOverride = $sproutBaseSitemapSettings['pluginNameOverride'];
            $settings->enableCustomSections = $sproutBaseSitemapSettings['enableCustomSections'];
            $settings->enableDynamicSitemaps = $sproutBaseSitemapSettings['enableDynamicSitemaps'];
            $settings->enableMultilingualSitemaps = $sproutBaseSitemapSettings['enableMultilingualSitemaps'];
            $settings->totalElementsPerSitemap = $sproutBaseSitemapSettings['totalElementsPerSitemap'];
            $settings->siteSettings = $sproutBaseSitemapSettings['siteSettings'];
            return $settings;
        }

        // Need to check for how we stored data in Sprout SEO schema and migrate things if we find them
        // @deprecate in future version
        $sproutSeoSettings = $projectConfig->get('plugins.sprout-seo.settings');

        if ($sproutSeoSettings &&
            isset($sproutSeoSettings['siteSettings']) &&
            !empty($sproutSeoSettings['siteSettings'])) {

            $settings->pluginNameOverride = $sproutSeoSettings['pluginNameOverride'];
            $settings->enableCustomSections = $sproutSeoSettings['enableCustomSections'];
            $settings->enableDynamicSitemaps = $sproutSeoSettings['enableDynamicSitemaps'];
            $settings->enableMultilingualSitemaps = $sproutSeoSettings['enableMultilingualSitemaps'];
            $settings->totalElementsPerSitemap = $sproutSeoSettings['totalElementsPerSitemap'];
            $settings->siteSettings = $sproutSeoSettings['siteSettings'];
            return $settings;
        }

        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190415_000001_adds_sprout_sitemaps_migration cannot be reverted.\n";
        return false;
    }
}
