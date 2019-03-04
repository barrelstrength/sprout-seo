<?php

namespace barrelstrength\sproutseo\migrations;

use craft\db\Migration;
use yii\db\Query;

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
        $this->checkOldRedirectElements();
        return true;
    }

    protected function checkOldRedirectElements()
    {
        $types = [
            0 => [
                'oldType' => 'barrelstrength\sproutseo\elements\Redirect',
                'newType' => 'barrelstrength\sproutredirects\elements\Redirect'
            ]
        ];

        foreach ($types as $type) {
            $this->update('{{%elements}}', [
                'type' => $type['newType']
            ], ['type' => $type['oldType']], [], false);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m190227_000000_sproutseo_editions cannot be reverted.\n";
        return false;
    }
}
