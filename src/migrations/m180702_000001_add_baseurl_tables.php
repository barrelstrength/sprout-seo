<?php

namespace barrelstrength\sproutseo\migrations;

use craft\db\Migration;
use craft\db\Query;
use Craft;

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
            'baseUrlId' => $this->integer(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createIndex(null, '{{%sproutseo_baseurl_sites}}', ['baseUrlId'], true);
        $this->addForeignKey(null, '{{%sproutseo_baseurl_sites}}', ['baseUrlId'], '{{%sproutseo_baseurls}}', ['id'], 'CASCADE', 'CASCADE');

        $sites = (new Query())
            ->select(['*'])
            ->from(['{{%sites}}'])
            ->all();
        $uniqueUrls = [];

        foreach ($sites as $site) {
            $url = isset($site['baseUrl']) ? Craft::getAlias($site['baseUrl']) : null;
            $url = rtrim($url,"/");

            // Exclude sites with not URL
            if ($url){
                if (!isset($uniqueUrls[$url])){
                    $uniqueUrls[$url] = $url;
                    $this->insert("{{%sproutseo_baseurls}}", ['baseUrl' => $url]);
                }
            }

            $baseUrl = (new Query())
                ->select(['*'])
                ->from(['{{%sproutseo_baseurls}}'])
                ->where(['baseUrl' => $url])
                ->one();

            $baseUrlId = $baseUrl['id'] ?? null;

            $this->insert("{{%sproutseo_baseurl_sites}}", [
                'siteId' => $site['id'],
                'baseUrlId' => $baseUrlId
            ]);
        }

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
