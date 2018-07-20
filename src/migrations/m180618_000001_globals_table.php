<?php

namespace barrelstrength\sproutseo\migrations;

use craft\db\Migration;
use craft\db\Query;
use craft\helpers\MigrationHelper;

/**
 * m180618_000001_globals_table migration.
 */
class m180618_000001_globals_table extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws \yii\base\NotSupportedException
     */
    public function safeUp()
    {
        $table = '{{%sproutseo_metadata_globals}}';
        $isNew = false;
        $primarySite = (new Query())
            ->select(['id'])
            ->from(['{{%sites}}'])
            ->where(['primary' => 1])
            ->one();

        $primarySiteId = $primarySite['id'];

        if (!$this->db->columnExists($table, 'siteId')) {
            $this->addColumn($table, 'siteId', $this->integer()->after('id')->notNull());
            $isNew = true;
        }

        $rows = (new Query())
            ->select(['id'])
            ->from([$table])
            ->all();

        foreach ($rows as $row) {
            $this->update($table, ['siteId' => $primarySiteId], ['id' => $row['id']], [], false);
        }

        if ($isNew) {
            $this->createIndex($this->db->getIndexName($table, 'id,siteId'), $table, 'id,siteId', true);
            $this->createIndex(null, '{{%sproutseo_metadata_globals}}', ['siteId'], true);
            $this->addForeignKey($this->db->getForeignKeyName($table, 'siteId'), $table, 'siteId', '{{%sites}}', 'id', 'CASCADE', 'CASCADE');
        }

        if ($this->db->columnExists($table, 'locale')) {
            MigrationHelper::dropIndexIfExists($table, ['id', 'locale'], true, $this);
            MigrationHelper::dropIndexIfExists($table, ['locale'], false, $this);
            MigrationHelper::dropForeignKeyIfExists($table, ['locale'], $this);
            $this->dropColumn($table, 'locale');
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