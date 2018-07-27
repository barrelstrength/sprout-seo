<?php

namespace barrelstrength\sproutseo\migrations;

use barrelstrength\sproutseo\elements\Redirect;
use craft\db\Migration;

/**
 * m180726_000000_remove_sections_table migration.
 */
class m180726_000000_remove_sections_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $table = "{{%sproutseo_metadata_sections}}";

        $this->dropTableIfExists($table);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180726_000000_remove_sections_table cannot be reverted.\n";
        return false;
    }
}
