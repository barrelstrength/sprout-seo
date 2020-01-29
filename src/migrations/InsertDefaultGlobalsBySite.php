<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;

use craft\db\Migration;

class InsertDefaultGlobalsBySite extends Migration
{
    /**
     * @var int|null The Site Id
     */
    public $siteId;

    /**
     * @inheritdoc
     *
     * @return bool|void
     * @throws \yii\db\Exception
     */
    public function safeUp()
    {
        $tableName = '{{%sproutseo_globals}}';

        $defaultSettings = '{
            "seoDivider":"-",
            "defaultOgType":"",
            "ogTransform":"sproutSeo-socialSquare",
            "twitterTransform":"sproutSeo-socialSquare",
            "defaultTwitterCard":"summary",
            "appendTitleValueOnHomepage":"",
            "appendTitleValue": ""}
        ';

        $this->db->createCommand()->insert($tableName, [
                'siteId' => $this->siteId,
                'identity' => null,
                'ownership' => null,
                'contacts' => null,
                'social' => null,
                'robots' => null,
                'settings' => $defaultSettings
            ]
        )->execute();
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        return false;
    }
}
