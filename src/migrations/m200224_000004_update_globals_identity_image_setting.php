<?php

/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;

use craft\db\Migration;
use craft\db\Query;

class m200224_000004_update_globals_identity_image_setting extends Migration
{
    /**
     * @return bool
     */
    public function safeUp(): bool
    {
        $globalsTable = '{{%sproutseo_globals}}';

        $globalSettings = (new Query())
            ->select(['id', 'identity'])
            ->from([$globalsTable])
            ->all();

        foreach ($globalSettings as $globalSetting) {
            $newImageValue = null;
            $oldIdentity = json_decode($globalSetting['identity'], true);
            $newIdentity = $oldIdentity;

            if ($oldIdentity !== null && is_array($oldIdentity)) {
                $oldImageValue = $oldIdentity['image'] ?? null;
                if ($oldImageValue && is_array($oldImageValue)) {
                    $newImageValue = $oldImageValue[0] ?? null;
                    $oldIdentity['image'] = $newImageValue;
                    $newIdentity = json_encode($oldIdentity);
                }
            }

            $this->update($globalsTable, [
                'identity' => $newIdentity
            ], [
                'id' => $globalSetting['id']
            ], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200224_000004_update_globals_identity_image_setting cannot be reverted.\n";

        return false;
    }
}
