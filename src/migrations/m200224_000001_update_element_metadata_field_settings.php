<?php /** @noinspection ClassConstantCanBeUsedInspection */

/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;

use craft\db\Migration;
use craft\db\Query;

class m200224_000001_update_element_metadata_field_settings extends Migration
{
    /**
     * @return bool
     */
    public function safeUp(): bool
    {
        $fields = (new Query())
            ->select(['*'])
            ->from(['{{%fields}}'])
            ->where(['type' => 'barrelstrength\\sproutseo\\fields\\ElementMetadata'])
            ->all();

        foreach ($fields as $field) {
            $settings = json_decode($field['settings'], true);
            unset($settings['metadata'], $settings['values'], $settings['showMainEntity']);
            $settingsAsJson = json_encode($settings);

            $this->update('{{%fields}}', [
                'settings' => $settingsAsJson
            ], [
                'id' => $field['id']
            ], [], false);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200224_000001_update_element_metadata_field_settings cannot be reverted.\n";

        return false;
    }
}
