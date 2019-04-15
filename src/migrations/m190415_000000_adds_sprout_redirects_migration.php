<?php

namespace barrelstrength\sproutseo\migrations;

use barrelstrength\sproutbaseredirects\migrations\Install as SproutBaseRedirectsInstall;
use craft\db\Migration;

/**
 * m190415_000000_adds_sprout_redirects_migration migration.
 */
class m190415_000000_adds_sprout_redirects_migration extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $migration = new SproutBaseRedirectsInstall();

        ob_start();
        $migration->safeUp();
        ob_end_clean();

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
}
