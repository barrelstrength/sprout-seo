<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;

use barrelstrength\sproutseo\records\GlobalMetadata as GlobalMetadataRecord;
use craft\db\Migration;
use yii\db\Exception;

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
     * @throws Exception
     */
    public function safeUp()
    {
        $defaultSettings = '{
            "seoDivider":"-",
            "defaultOgType":"website",
            "ogTransform":"sproutSeo-socialSquare",
            "twitterTransform":"sproutSeo-socialSquare",
            "defaultTwitterCard":"summary",
            "appendTitleValueOnHomepage":"",
            "appendTitleValue": ""}
        ';

        $this->db->createCommand()->insert(GlobalMetadataRecord::tableName(), [
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
