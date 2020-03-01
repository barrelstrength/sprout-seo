<?php

/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;

use craft\db\Migration;

/**
 * m180719_000000_redirect_elements_types migration.
 */
class m180719_000000_redirect_elements_types extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $types = [
            0 => [
                'oldType' => 'SproutSeo_Redirect',
                'newType' => 'barrelstrength\sproutseo\elements\Redirect'
            ]
        ];

        foreach ($types as $type) {
            $this->update('{{%elements}}', [
                'type' => $type['newType']
            ], ['type' => $type['oldType']], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m180719_000000_redirect_elements_types cannot be reverted.\n";

        return false;
    }
}
