<?php /**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

/** @noinspection ClassConstantCanBeUsedInspection */

namespace barrelstrength\sproutseo\migrations;

use Craft;
use craft\db\Migration;
use craft\services\Plugins;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\web\ServerErrorHttpException;

class m200420_000000_update_settings_model extends Migration
{
    /**
     * @return bool
     * @throws ErrorException
     * @throws Exception
     * @throws NotSupportedException
     * @throws ServerErrorHttpException
     */
    public function safeUp(): bool
    {
        // Don't make the same config changes twice
        $projectConfig = Craft::$app->getProjectConfig();
        $pluginHandle = 'sprout-seo';
        $schemaVersion = $projectConfig->get(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.schemaVersion', true);
        if (version_compare($schemaVersion, '4.5.0', '>=')) {
            return true;
        }

        $pluginSettings = Craft::$app->getProjectConfig()->get(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.settings');

        $metadataVariable = $pluginSettings['metadataVariable'] ?? false;
        $useMetadataVariableFallback = $metadataVariable ? true : false;
        $enableRenderMetadata = $pluginSettings['enableMetadataRendering'] ?? false;

        // Update settings to new names
        $pluginSettings['useMetadataVariable'] = $pluginSettings['toggleMetadataVariable'] ?? $useMetadataVariableFallback;
        $pluginSettings['enableRenderMetadata'] = $enableRenderMetadata ? true : false;
        $pluginSettings['metadataVariableName'] = $pluginSettings['metadataVariable'] ?? 'metadata';

        // Remove old settings
        unset(
            $pluginSettings['toggleMetadataVariable'],
            $pluginSettings['enableMetadataRendering'],
            $pluginSettings['metadataVariable'],
            $pluginSettings['appendTitleValue']
        );

        Craft::$app->getProjectConfig()->set(Plugins::CONFIG_PLUGINS_KEY.'.'.$pluginHandle.'.settings', $pluginSettings, 'Updated Sprout SEO settings.');

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200420_000000_update_settings_model cannot be reverted.\n";

        return false;
    }
}
