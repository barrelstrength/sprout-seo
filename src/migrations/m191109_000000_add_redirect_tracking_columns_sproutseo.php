<?php

namespace barrelstrength\sproutseo\migrations;

use barrelstrength\sproutbasereports\migrations\m191109_000000_add_redirect_tracking_columns;
use craft\db\Migration;
use Throwable;

/**
 * m191109_000000_add_redirect_tracking_columns_sproutseo migration.
 */
class m191109_000000_add_redirect_tracking_columns_sproutseo extends Migration
{
    /**
     * @inheritdoc
     *
     * @return bool
     * @throws Throwable
     */
    public function safeUp(): bool
    {
        $migration = new m191109_000000_add_redirect_tracking_columns();

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
        echo "m191109_000000_add_redirect_tracking_columns_sproutseo cannot be reverted.\n";
        return false;
    }
}
