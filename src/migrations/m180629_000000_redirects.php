<?php

namespace barrelstrength\sproutseo\migrations;

use craft\db\Migration;
use craft\db\Query;


/**
 * m180629_000000_redirects migration.
 */
class m180629_000000_redirects extends Migration
{
    /**
     * @inheritdoc
     *
     * @throws \yii\base\NotSupportedException
     */
    public function safeUp()
    {
        $table = '{{%sproutseo_redirects}}';
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
            $this->addForeignKey($this->db->getForeignKeyName($table, 'siteId'), $table, 'siteId', '{{%sites}}', 'id', 'CASCADE', 'CASCADE');
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180629_000000_redirects cannot be reverted.\n";
        return false;
    }
}