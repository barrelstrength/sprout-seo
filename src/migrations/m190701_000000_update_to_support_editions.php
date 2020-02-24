<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;

use barrelstrength\sproutseo\SproutSeo;
use Craft;
use craft\db\Migration;
use craft\errors\InvalidPluginException;
use Throwable;

/**
 * m190701_000000_update_to_support_editions migration.
 */
class m190701_000000_update_to_support_editions extends Migration
{
    /**
     * @inheritdoc
     *
     * @return bool
     * @throws Throwable
     * @throws InvalidPluginException
     */
    public function safeUp(): bool
    {
        // Don't make the same config changes twice
        $projectConfig = Craft::$app->getProjectConfig();
        $schemaVersion = $projectConfig->get('plugins.sprout-seo.schemaVersion', true);

        if (version_compare($schemaVersion, '4.1.0', '>=')) {
            return true;
        }

        Craft::$app->getPlugins()->switchEdition('sprout-seo', SproutSeo::EDITION_PRO);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190701_000000_update_to_support_editions cannot be reverted.\n";

        return false;
    }
}
