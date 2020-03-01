<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;

use barrelstrength\sproutbaseuris\sectiontypes\Category;
use barrelstrength\sproutbaseuris\sectiontypes\Entry;
use barrelstrength\sproutbaseuris\sectiontypes\Product;
use craft\db\Migration;
use craft\db\Query;
use yii\base\NotSupportedException;

/**
 * m180625_000000_sections_to_sitemap migration.
 */
class m180625_000000_sections_to_sitemap extends Migration
{
    /**
     * @return bool
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $table = '{{%sproutseo_sitemaps}}';

        $sitemapsTableExists = $this->getDb()->tableExists($table);

        if (!$sitemapsTableExists) {
            $this->createTable($table, [
                'id' => $this->primaryKey(),
                'siteId' => $this->integer()->notNull(),
                'urlEnabledSectionId' => $this->integer(),
                'enabled' => $this->boolean()->defaultValue(false),
                'type' => $this->string(),
                'uri' => $this->string(),
                'priority' => $this->decimal(11, 1),
                'changeFrequency' => $this->string(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);

            $this->createIndex(null, $table, ['siteId']);
            $this->addForeignKey(null, $table, ['siteId'], '{{%sites}}', ['id'], 'CASCADE', 'CASCADE');
        }

        if (!$this->db->columnExists($table, 'uri')) {
            $this->addColumn($table, 'uri', $this->string()->after('type'));
        }

        $primarySite = (new Query())
            ->select(['id'])
            ->from(['{{%sites}}'])
            ->where(['primary' => 1])
            ->one();

        $primarySiteId = $primarySite['id'];

        $sections = [];
        if ($this->db->tableExists('{{%sproutseo_metadata_sections}}')) {
            $sections = (new Query())
                ->select(['*'])
                ->from(['{{%sproutseo_metadata_sections}}'])
                ->all();
        }

        foreach ($sections as $section) {
            $newType = null;

            switch ($section['type']) {
                case 'entries':
                    $newType = Entry::class;
                    break;
                case 'categories':
                    $newType = Category::class;
                    break;
                case 'commerce_products':
                    $newType = Product::class;
                    break;
            }

            $sitemapData = [
                'siteId' => $primarySiteId,
                'urlEnabledSectionId' => $section['urlEnabledSectionId'],
                'enabled' => $section['enabled'],
                'type' => $newType ?? $section['type'],
                'uri' => $section['uri'] ?? null,
                'priority' => $section['priority'],
                'changeFrequency' => $section['changeFrequency'],
            ];

            $this->insert($table, $sitemapData);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180625_000000_sections_to_sitemap cannot be reverted.\n";

        return false;
    }
}