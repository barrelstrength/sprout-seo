<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;

use barrelstrength\sproutbaseredirects\migrations\Install as SproutBaseRedirectsInstall;
use barrelstrength\sproutbaseredirects\models\Settings as SproutRedirectsSettings;
use barrelstrength\sproutbaseredirects\SproutBaseRedirects;
use Craft;
use craft\db\Migration;
use craft\db\Query;
use Throwable;
use yii\db\Exception;

class m200307_000000_removes_enable_globals_setting extends Migration
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

        $projectConfig->remove('plugins.sprout-seo.settings.enableGlobals');

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200307_000000_removes_enable_globals_setting cannot be reverted.\n";

        return false;
    }

}
