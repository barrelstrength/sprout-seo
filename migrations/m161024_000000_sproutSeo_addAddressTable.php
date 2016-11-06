<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m161024_000000_sproutSeo_addAddressTable extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName = 'sproutseo_addresses';

		SproutSeoPlugin::log("Creating the {$tableName} table.");

		craft()->db->createCommand()->createTable($tableName, array(
			'id'                 => array('column' => ColumnType::PK, 'null' => false),
			'modelId'            => array('column' => ColumnType::Int, 'length' => 10, 'null' => true),
			'countryCode'        => array('column' => ColumnType::Varchar, 'length' => 255, 'null' => true),
			'administrativeArea' => array('column' => ColumnType::Varchar, 'length' => 255, 'null' => true),
			'locality'           => array('column' => ColumnType::Varchar, 'length' => 255, 'null' => true),
			'dependentLocality'  => array('column' => ColumnType::Varchar, 'length' => 255, 'null' => true),
			'postalCode'         => array('column' => ColumnType::Varchar, 'length' => 255, 'null' => true),
			'sortingCode'        => array('column' => ColumnType::Varchar, 'length' => 255, 'null' => true),
			'address1'           => array('column' => ColumnType::Varchar, 'length' => 255, 'null' => true),
			'address2'           => array('column' => ColumnType::Varchar, 'length' => 255, 'null' => true),
			'dateCreated'        => array('column' => ColumnType::DateTime, 'null' => false),
			'dateUpdated'        => array('column' => ColumnType::DateTime, 'null' => false),
			'uid'                => array('column' => 'char(36)', 'null' => false, 'default' => '0'),
		), null, true, false);

		SproutSeoPlugin::log("Finished creating the {$tableName} table.");

		return true;
	}
}
