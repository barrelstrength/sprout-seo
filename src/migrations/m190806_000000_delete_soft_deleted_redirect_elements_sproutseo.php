<?php

namespace barrelstrength\sproutseo\migrations;

use barrelstrength\sproutbaseredirects\migrations\m190806_000000_delete_soft_deleted_redirect_elements;
use craft\db\Migration;
use Throwable;

/**
 * m190806_000000_delete_soft_deleted_redirect_elements_sproutseo migration.
 */
class m190806_000000_delete_soft_deleted_redirect_elements_sproutseo extends Migration
{
    /**
     * @inheritdoc
     *
     * @return bool
     * @throws Throwable
     */
    public function safeUp(): bool
    {
        $migration = new m190806_000000_delete_soft_deleted_redirect_elements();

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
        echo "m190806_000000_delete_soft_deleted_redirect_elements_sproutseo cannot be reverted.\n";

        return false;
    }
}
