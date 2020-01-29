<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;

use craft\db\Migration;

/**
 * m180617_000000_renames_globals_table migration.
 */
class m180617_000000_renames_globals_table extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws \yii\base\NotSupportedException
     */
    public function safeUp()
    {
        $oldTable = '{{%sproutseo_metadata_globals}}';
        $newTable = '{{%sproutseo_globals}}';

        if ($this->db->tableExists($oldTable) && !$this->db->tableExists($newTable)) {
            $this->renameTable($oldTable, $newTable);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180617_000000_renames_globals_table cannot be reverted.\n";

        return false;
    }
}