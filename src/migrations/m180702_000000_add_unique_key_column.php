<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;

use barrelstrength\sproutbasesitemaps\SproutBaseSitemaps;
use craft\db\Migration;
use craft\db\Query;
use yii\base\Exception;
use yii\base\NotSupportedException;

/**
 * m180702_000000_add_unique_key_column migration.
 */
class m180702_000000_add_unique_key_column extends Migration
{
    /**
     * @inheritdoc
     *
     * @return bool
     * @throws Exception
     * @throws NotSupportedException
     */
    public function safeUp(): bool
    {
        $table = '{{%sproutseo_sitemaps}}';

        if (!$this->db->columnExists($table, 'uniqueKey')) {
            $this->addColumn($table, 'uniqueKey', $this->text()->after('siteId'));
        }

        $sitemaps = (new Query())
            ->select('*')
            ->from(['{{%sproutseo_sitemaps}}'])
            ->all();

        foreach ($sitemaps as $sitemap) {
            $uniqueKey = SproutBaseSitemaps::$app->sitemaps->generateUniqueKey();
            $this->update($table, ['uniqueKey' => $uniqueKey], ['id' => $sitemap['id']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180702_000000_add_unique_key_column cannot be reverted.\n";

        return false;
    }
}
