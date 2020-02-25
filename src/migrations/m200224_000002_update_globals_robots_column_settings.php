<?php

/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;

use craft\db\Migration;
use craft\db\Query;

class m200224_000002_update_globals_robots_column_settings extends Migration
{
    /**
     * @return bool
     */
    public function safeUp(): bool
    {
        $globalsTable = '{{%sproutseo_globals}}';

        $globalSettings = (new Query())
            ->select(['id', 'robots'])
            ->from([$globalsTable])
            ->all();

        foreach ($globalSettings as $globalSetting) {
            $robotsValue = null;
            $robots = json_decode($globalSetting['robots'], true);

            if ($robots !== null && is_array($robots)) {
                $enabledRobotsSettings = array_filter($robots);
                $robotsValues = array_keys($enabledRobotsSettings);
                $robotsAsString = implode(',',$robotsValues);
            }

            if (isset($robotsAsString) && $robotsAsString !== null) {
                $robotsValue = json_encode($robotsAsString);
            }

            $this->update($globalsTable, [
                'robots' => $robotsValue
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
        echo "m200224_000002_update_globals_robots_column_settings cannot be reverted.\n";

        return false;
    }
}
