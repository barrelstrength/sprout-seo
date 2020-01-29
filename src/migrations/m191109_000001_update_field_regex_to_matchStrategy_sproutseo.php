<?php

namespace barrelstrength\sproutseo\migrations;

use barrelstrength\sproutbaseredirects\migrations\m191109_000001_update_field_regex_to_matchStrategy;
use craft\db\Migration;
use Throwable;

/**
 * m191109_000001_update_field_regex_to_matchStrategy_sproutseo migration.
 */
class m191109_000001_update_field_regex_to_matchStrategy_sproutseo extends Migration
{
    /**
     * @inheritdoc
     *
     * @return bool
     * @throws Throwable
     */
    public function safeUp(): bool
    {
        $migration = new m191109_000001_update_field_regex_to_matchStrategy();

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
        echo "m191109_000001_update_field_regex_to_matchStrategy_sproutseo cannot be reverted.\n";

        return false;
    }
}
