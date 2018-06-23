<?php

namespace barrelstrength\sproutseo\migrations;

use craft\db\Migration;
use craft\db\Query;
use Craft;

/**
 * m180618_000001_globals_table migration.
 */
class m180618_000001_globals_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $table = "{{%sproutseo_metadata_globals}}";

        if (!$this->db->columnExists($table, 'siteId')) {

            $this->addColumn($table, 'siteId', $this->integer()->after('elementId')->notNull());
            $isNew = true;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180618_000001_globals_table cannot be reverted.\n";
        return false;
    }
}