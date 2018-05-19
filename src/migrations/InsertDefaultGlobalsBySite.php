<?php

namespace barrelstrength\sproutseo\migrations;

use craft\db\Migration;

class InsertDefaultGlobalsBySite extends Migration
{
    // Properties
    // =========================================================================

    /**
     * @var int|null The Site Id
     */
    public $siteId;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableName = '{{%sproutseo_metadata_globals}}';

        $defaultSettings = '{
            "seoDivider":"-",
            "defaultOgType":"",
            "ogTransform":"sproutSeo-socialSquare",
            "twitterTransform":"sproutSeo-socialSquare",
            "defaultTwitterCard":"summary",
            "appendTitleValueOnHomepage":"",
            "appendTitleValue": ""}
        ';

        $result = $this->db->createCommand()->insert($tableName, [
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
    public function safeDown()
    {
        return false;
    }
}
