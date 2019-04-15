<?php

namespace barrelstrength\sproutseo\migrations;

use barrelstrength\sproutbasesitemaps\migrations\Install as SproutBaseSitemapsInstall;
use craft\db\Migration;

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

        return true;
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
