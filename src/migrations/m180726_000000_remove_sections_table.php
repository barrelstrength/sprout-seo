<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;

use craft\db\Migration;

/**
 * m180726_000000_remove_sections_table migration.
 */
class m180726_000000_remove_sections_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $table = "{{%sproutseo_metadata_sections}}";

        $this->dropTableIfExists($table);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180726_000000_remove_sections_table cannot be reverted.\n";

        return false;
    }
}
