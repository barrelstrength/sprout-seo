<?php

namespace barrelstrength\sproutseo\migrations;

use craft\db\Migration;
use craft\db\Query;

use Craft;
use craft\helpers\MigrationHelper;

/**
 * m180620_000000_craft2_to_craft3 migration.
 */
class m180620_000000_craft2_to_craft3 extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180620_000000_craft2_to_craft3 cannot be reverted.\n";
        return false;
    }
}