<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;

use Craft;
use craft\db\Migration;

class m200307_000000_removes_sproutseo_enable_redirects_setting extends Migration
{
    /**
     * @return bool
     */
    public function safeUp(): bool
    {
        // Don't make the same config changes twice
        $projectConfig = Craft::$app->getProjectConfig();
        $schemaVersion = $projectConfig->get('plugins.sprout-seo.schemaVersion', true);

        if (version_compare($schemaVersion, '4.3.2', '>=')) {
            return true;
        }

        $projectConfig->remove('plugins.sprout-seo.settings.enableRedirects');

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200307_000000_removes_sproutseo_enable_redirects_setting cannot be reverted.\n";

        return false;
    }

}
