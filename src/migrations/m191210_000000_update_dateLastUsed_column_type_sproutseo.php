<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;

use barrelstrength\sproutbaseredirects\migrations\m191210_000000_update_dateLastUsed_column_type;
use craft\db\Migration;
use Throwable;

/**
 * m191210_000000_update_dateLastUsed_column_type_sproutseo migration.
 */
class m191210_000000_update_dateLastUsed_column_type_sproutseo extends Migration
{
    /**
     * @inheritdoc
     *
     * @return bool
     * @throws Throwable
     */
    public function safeUp(): bool
    {
        $migration = new m191210_000000_update_dateLastUsed_column_type();

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
        echo "m191210_000000_update_dateLastUsed_column_type_sproutseo cannot be reverted.\n";

        return false;
    }
}
