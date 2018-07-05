<?php

namespace barrelstrength\sproutseo\migrations;

use craft\db\Migration;
use craft\db\Query;
use Craft;

/**
 * m180705_000000_add_redirects_sites migration.
 */
class m180705_000000_add_redirects_sites extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $table = '{{%sproutseo_redirects}}';

        if (!$this->db->columnExists($table, 'baseUrlSiteId')) {
            $this->addColumn($table, 'baseUrlSiteId', $this->integer()->after('id'));

            $this->createIndex(null, '{{%sproutseo_redirects}}', 'id, baseUrlSiteId', true);
            $this->addForeignKey(null, '{{%sproutseo_redirects}}', ['baseUrlSiteId'], '{{%sproutseo_baseurl_sites}}', ['id'], 'CASCADE', 'CASCADE');
        }

        $redirects = (new Query())
            ->select('*')
            ->from([$table])
            ->all();

        $primarySite = (new Query())
            ->select(['id'])
            ->from(['{{%sites}}'])
            ->where(['primary' => 1])
            ->one();

        $primarySiteId = $primarySite['id'];

        $baseUrlSite = (new Query())
            ->select(['id'])
            ->from(['{{%sproutseo_baseurl_sites}}'])
            ->where(['siteId' => $primarySiteId])
            ->one();

        if ($baseUrlSite){
            foreach ($redirects as $redirect) {
                $this->update($table, ['baseUrlSiteId' => $baseUrlSite['id']], ['id' => $redirect['id']], [], false);
            }
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180705_000000_add_redirects_sites cannot be reverted.\n";

        return false;
    }
}
