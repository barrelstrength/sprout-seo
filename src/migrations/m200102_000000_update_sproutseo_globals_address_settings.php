<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\migrations;

use barrelstrength\sproutbasefields\migrations\m200102_000000_update_sproutseo_globals_address;
use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;
use yii\db\Exception;

/**
 * This migration works alongside the migration of the same name in Sprout Base Fields
 * barrelstrength\sproutbasefields\migrations\m200102_000000_update_sproutseo_globals_address
 */
class m200102_000000_update_sproutseo_globals_address_settings extends Migration
{
    /**
     * @return bool
     * @throws Exception
     */
    public function safeUp(): bool
    {
        // Migrate any address to a temporary settings table
        $migration = new m200102_000000_update_sproutseo_globals_address();

        ob_start();
        $migration->safeUp();
        ob_end_clean();

        $sproutSettingsTable = '{{%sprout_settings}}';
        $sproutSeoGlobalsTable = '{{%sproutseo_globals}}';

        // Get the updated addresses from the temporary settings table
        $globalMetadataAddresses = (new Query())
            ->select(['*'])
            ->from([$sproutSettingsTable])
            ->where(['like', 'model', 'address-fields-migration-sprout-seo-v4.2.9'])
            ->indexBy('model')
            ->all();

        // Loop through our identities and update them all with the migrated addresses
        $globalSettings = (new Query())
            ->select(['id', 'siteId', 'identity'])
            ->from([$sproutSeoGlobalsTable])
            ->all();

        $settingsTableRowIds = [];
        foreach ($globalSettings as $settings) {

            $addressData = $globalMetadataAddresses['address-fields-migration-sprout-seo-v4.2.9-siteId:'.$settings['siteId']] ?? null;
            if ($addressData === null) {
                continue;
            }

            $settingsTableRowIds[] = $addressData['id'];

            $address = Json::decode($addressData['settings']);
            unset(
                $address['id'],
                $address['siteId'],
                $address['elementId'],
                $address['fieldId'],
                $address['delete']
            );

            $identity = Json::decode($settings['identity']);
            $identity['address'] = $address;
            unset($identity['addressId']);

            $this->update($sproutSeoGlobalsTable, [
                'identity' => Json::encode($identity)
            ], [
                'id' => $settings['id']
            ], [], false);
        }

        $this->delete($sproutSettingsTable, [
            'in', 'id', $settingsTableRowIds
        ]);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "m200102_000000_update_sproutseo_globals_address_settings cannot be reverted.\n";

        return false;
    }
}
