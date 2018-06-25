<?php

namespace barrelstrength\sproutseo\migrations;

use craft\db\Migration;
use craft\db\Query;
use barrelstrength\sproutbase\app\fields\migrations\Install as SproutBaseFieldsInstall;

/**
 * m180625_000001_address_table migration.
 */
class m180625_000001_address_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createAddressTable();

        $addresses = (new Query())
            ->select(['*'])
            ->from(['{{%sproutseo_addresses}}'])
            ->all();

        $primarySite = (new Query())
            ->select(['id'])
            ->from(['{{%sites}}'])
            ->where(['primary' => 1])
            ->one();

        $primarySiteId = $primarySite['id'];

        foreach ($addresses as $address) {
            $addressData = [
                'elementId' => null,
                'siteId' => $primarySiteId,
                'fieldId' => null,
                'countryCode' => $address['countryCode'],
                'administrativeArea' => $address['administrativeArea'],
                'locality' => $address['locality'],
                'dependentLocality' => $address['dependentLocality'],
                'postalCode' => $address['postalCode'],
                'sortingCode' => $address['sortingCode'],
                'address1' => $address['address1'],
                'address2' => $address['address2'],
            ];

            $this->insert('{{%sproutfields_addresses}}', $addressData);
            $addressId = $this->db->getLastInsertID();

            $globals = (new Query())
                ->select(['id','identity'])
                ->from(['{{%sproutseo_metadata_globals}}'])
                ->one();

            if (isset($globals['identity']) && isset($globals['id'])){
                $identity = json_decode($globals['identity'], true);
                if (isset($identity['addressId'])){
                    $identity['addressId'] = $addressId;
                    $this->update('{{%sproutseo_metadata_globals}}', ['identity' => json_encode($identity)], ['id' => $globals['id']], [], false);
                }
            }
        }

        return true;
    }

    protected function createAddressTable()
    {
        $migration = new SproutBaseFieldsInstall();

        ob_start();
        $migration->up();
        ob_end_clean();
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m180625_000001_address_table cannot be reverted.\n";
        return false;
    }
}