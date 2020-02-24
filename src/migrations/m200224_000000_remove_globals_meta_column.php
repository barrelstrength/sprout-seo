<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;

use craft\db\Migration;
use yii\base\NotSupportedException;

class m200224_000000_remove_globals_meta_column extends Migration
{
    /**
     * @return bool
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        if ($this->db->columnExists('{{%sproutseo_globals}}', 'meta')) {
            $this->dropColumn('{{%sproutseo_globals}}', 'meta');
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200224_000000_remove_globals_meta_column cannot be reverted.\n";

        return false;
    }
}
