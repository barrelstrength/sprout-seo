<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;

use craft\db\Migration;

/**
 * m200204_000000_remove_metadata_elements_table migration.
 */
class m200204_000000_remove_metadata_elements_table extends Migration
{
    /**
     * @return bool
     */
    public function safeUp(): bool
    {
        $this->dropTableIfExists('{{%sproutseo_metadata_elements}}');

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200204_000000_remove_metadata_elements_table cannot be reverted.\n";

        return false;
    }
}
