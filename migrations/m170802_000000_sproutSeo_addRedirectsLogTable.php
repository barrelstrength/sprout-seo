<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m170802_000000_sproutSeo_addRedirectsLogTable extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName = 'sproutseo_redirects_log';

		SproutSeoPlugin::log("Creating the {$tableName} table.");

		craft()->db->createCommand()->createTable($tableName, array(
			'id'                 => array('column' => ColumnType::PK, 'null' => false),
			'redirectId'         => array('column' => ColumnType::Int, 'length' => 10, 'null' => true),
			'referralURL'        => array('column' => ColumnType::Varchar, 'length' => 255, 'null' => true),
			'ipAddress'          => array('column' => ColumnType::Varchar, 'length' => 255, 'null' => true),
			'dateCreated'        => array('column' => ColumnType::DateTime, 'null' => false),
			'dateUpdated'        => array('column' => ColumnType::DateTime, 'null' => false),
			'uid'                => array('column' => 'char(36)', 'null' => false, 'default' => '0'),
		), null, true, false);

		craft()->db->createCommand()->addForeignKey($tableName, 'redirectId', 'sproutseo_redirects', 'id', 'CASCADE');

		SproutSeoPlugin::log("Finished creating the {$tableName} table.");

		return true;
	}
}
