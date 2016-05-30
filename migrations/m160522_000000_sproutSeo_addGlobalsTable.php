<?php
namespace Craft;

class m160522_000000_sproutSeo_addGlobalsTable extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName = 'sproutseo_globals';

		if (!craft()->db->tableExists($tableName))
		{
			SproutSeoPlugin::log("Creating the {$tableName} table.");

			craft()->db->createCommand()->createTable($tableName, array(
				'id' => array('column' => ColumnType::PK, 'null' => false),
				'locale'    => array('column' => ColumnType::Locale, 'null' => false),
				'identity'  => array('column' => ColumnType::Text, 'null' => true),
				'ownership' => array('column' => ColumnType::Text, 'null' => true),
				'contacts'  => array('column' => ColumnType::Text, 'null' => true),
				'social'    => array('column' => ColumnType::Text, 'null' => true),
				'dateCreated' => array('column' => ColumnType::DateTime, 'null' => false),
				'dateUpdated' => array('column' => ColumnType::DateTime, 'null' => false),
				'uid'         => array('column' => 'char(36)', 'null' => false, 'default' => '0'),
			), null, true, false
			);

			craft()->db->createCommand()->createIndex($tableName, 'id,locale', true);

			SproutSeoPlugin::log("Finished creating the {$tableName} table.");
		}
		else
		{
			SproutSeoPlugin::log("The {$tableName} table already exists", LogLevel::Info, true);
		}

		return true;
	}
}
