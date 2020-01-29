<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;

use barrelstrength\sproutbaseredirects\migrations\Install as SproutBaseRedirectsInstall;
use barrelstrength\sproutbaseredirects\models\Settings as SproutRedirectsSettings;
use barrelstrength\sproutbaseredirects\SproutBaseRedirects;
use Craft;
use craft\db\Migration;
use craft\db\Query;

/**
 * m190415_000000_adds_sprout_redirects_migration migration.
 *
 * @property \barrelstrength\sproutbaseredirects\models\Settings $sproutRedirectsSettingsModel
 */
class m190415_000000_adds_sprout_redirects_migration extends Migration
{
    /**
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function safeUp(): bool
    {
        $migration = new SproutBaseRedirectsInstall();

        ob_start();
        $migration->safeUp();
        ob_end_clean();

        $settingsRow = (new Query())
            ->select(['*'])
            ->from(['{{%sprout_settings}}'])
            ->where(['model' => SproutRedirectsSettings::class])
            ->one();

        $defaultSettings = json_decode($settingsRow['settings'], true);
        $defaultStructureId = $defaultSettings['structureId'] ?? null;

        // See if we already have settings
        $settings = $this->getSproutRedirectsSettingsModel();

        if ($settings->structureId) {
            // Add our default plugin settings
            SproutBaseRedirects::$app->settings->saveRedirectsSettings($settings->toArray());

            // Delete the Structure we created because another one already exists
            Craft::$app->structures->deleteStructureById($defaultStructureId);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190415_000000_adds_sprout_redirects_migration cannot be reverted.\n";

        return false;
    }

    /**
     * @return SproutRedirectsSettings
     */
    private function getSproutRedirectsSettingsModel(): SproutRedirectsSettings
    {
        $projectConfig = Craft::$app->getProjectConfig();
        $settings = new SproutRedirectsSettings();
        $pluginHandle = 'sprout-base-redirects';

        $sproutBaseRedirectSettings = $projectConfig->get('plugins.'.$pluginHandle.'.settings');

        if ($sproutBaseRedirectSettings &&
            isset($sproutBaseRedirectSettings['structureId']) &&
            is_numeric($sproutBaseRedirectSettings['structureId'])) {

            $settings->pluginNameOverride = $sproutBaseRedirectSettings['pluginNameOverride'] ?? null;
            $settings->structureId = $sproutBaseRedirectSettings['structureId'];
            $settings->enable404RedirectLog = $sproutBaseRedirectSettings['enable404RedirectLog'] ?? null;
            $settings->total404Redirects = $sproutBaseRedirectSettings['total404Redirects'] ?? null;

            return $settings;
        }

        // Need to check for how we stored data in Sprout SEO schema and migrate things if we find them
        // @deprecate in future version
        $sproutSeoSettings = $projectConfig->get('plugins.sprout-seo.settings');

        if ($sproutSeoSettings &&
            isset($sproutSeoSettings['structureId']) &&
            is_numeric($sproutSeoSettings['structureId'])) {

            $settings->pluginNameOverride = $sproutSeoSettings['pluginNameOverride'] ?? null;
            $settings->structureId = $sproutSeoSettings['structureId'];
            $settings->enable404RedirectLog = $sproutSeoSettings['enable404RedirectLog'] ?? null;
            $settings->total404Redirects = $sproutSeoSettings['total404Redirects'] ?? null;

            return $settings;
        }

        return $settings;
    }
}
