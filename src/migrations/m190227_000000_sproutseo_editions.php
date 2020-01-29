<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;

use craft\db\Migration;

/**
 * m190227_000000_sproutseo_editions migration.
 */
class m190227_000000_sproutseo_editions extends Migration
{
    /**
     * @inheritdoc
     *
     * @return bool
     */
    public function safeUp(): bool
    {
        $this->updateOldRedirectElements();
        $this->updateOldSitemaps();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190227_000000_sproutseo_editions cannot be reverted.\n";

        return false;
    }

    protected function updateOldSitemaps()
    {
        $types = [
            0 => [
                'oldType' => 'barrelstrength\sproutseo\sectiontypes\Entry',
                'newType' => 'barrelstrength\sproutbaseuris\sectiontypes\Entry'
            ],
            1 => [
                'oldType' => 'barrelstrength\sproutseo\sectiontypes\Category',
                'newType' => 'barrelstrength\sproutbaseuris\sectiontypes\Category'
            ],
            2 => [
                'oldType' => 'barrelstrength\sproutseo\sectiontypes\NoSection',
                'newType' => 'barrelstrength\sproutbaseuris\sectiontypes\NoSection'
            ],
            3 => [
                'oldType' => 'barrelstrength\sproutseo\sectiontypes\Product',
                'newType' => 'barrelstrength\sproutbaseuris\sectiontypes\Product'
            ],
        ];

        foreach ($types as $type) {
            $this->update('{{%sproutseo_sitemaps}}', [
                'type' => $type['newType']
            ], ['type' => $type['oldType']], [], false);
        }
    }

    protected function updateOldRedirectElements()
    {
        $types = [
            0 => [
                'oldType' => 'barrelstrength\sproutseo\elements\Redirect',
                'newType' => 'barrelstrength\sproutbaseredirects\elements\Redirect'
            ]
        ];

        foreach ($types as $type) {
            $this->update('{{%elements}}', [
                'type' => $type['newType']
            ], ['type' => $type['oldType']], [], false);
        }
    }
}
