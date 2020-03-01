<?php

/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;

use craft\db\Migration;
use craft\db\Query;

class m200224_000003_update_sitename_setting extends Migration
{
    /**
     * @return bool
     */
    public function safeUp(): bool
    {
        $globalsTable = '{{%sproutseo_globals}}';

        $globalSettings = (new Query())
            ->select(['id', 'settings'])
            ->from([$globalsTable])
            ->all();

        foreach ($globalSettings as $globalSetting) {
            $oldSettings = json_decode($globalSetting['settings'], true);
            $newSettings = $oldSettings;

            // Match all lowercase instances of sitename and update them to use shorthand object syntax
            if ($oldSettings !== null && is_array($oldSettings)) {
                $oldAppendTitleValue = $oldSettings['appendTitleValue'];
                $newAppendTitleValue = str_replace('sitename', '{siteName}', $oldAppendTitleValue);
                $newSettings['appendTitleValue'] = $newAppendTitleValue;
            }

            $this->update($globalsTable, [
                'settings' =>  json_encode($newSettings)
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
        echo "m200224_000003_update_sitename_setting cannot be reverted.\n";

        return false;
    }
}
