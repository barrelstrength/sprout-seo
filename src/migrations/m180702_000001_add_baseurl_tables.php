<?php

namespace barrelstrength\sproutseo\migrations;

use craft\db\Migration;

/**
 * m180702_000001_add_baseurl_tables migration.
 */
class m180702_000001_add_baseurl_tables extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('{{%sproutseo_baseurls}}', [
            'id' => $this->primaryKey(),
            'baseUrl' => $this->string()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable('{{%sproutseo_baseurl_sites}}', [
            'id' => $this->primaryKey(),
            'siteId' => $this->integer()->notNull(),
            'baseUrlId' => $this->integer()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%sproutseo_baseurl_sites}}', ['baseUrlId'], true);
        $this->addForeignKey(null, '{{%sproutseo_baseurl_sites}}', ['baseUrlId'], '{{%sproutseo_baseurls}}', ['id'], 'CASCADE', 'CASCADE');

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180702_000001_add_baseurl_tables cannot be reverted.\n";

        return false;
    }
}
