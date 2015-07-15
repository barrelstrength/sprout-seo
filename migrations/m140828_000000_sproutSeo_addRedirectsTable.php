<?php
namespace Craft;

class m140828_000000_sproutSeo_addRedirectsTable extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName = 'sproutseo_redirects';
		if (!craft()->db->tableExists($tableName))
		{
			SproutSeoPlugin::log("Creating the {$tableName} table.");

			craft()->db->createCommand()->createTable($tableName, array(
				'id'   			   => array('column' => ColumnType::Int, 'null' => false),
				'oldUrl'       => array('column' => ColumnType::Varchar, 'null' => false),
				'newUrl'       => array('column' => ColumnType::Varchar, 'null' => false),
				'method'       => array('column' => 'int(10)', 'null' => false),
				'regex'        => array('column' => ColumnType::TinyInt, 'length' => 1, 'null' => false, 'default' => 0,
				                        'unsigned' => true),
				'dateCreated'  => array('column' => ColumnType::DateTime, 'null' => false),
				'dateUpdated'  => array('column' => ColumnType::DateTime, 'null' => false),
				'uid'      		 => array('column' => 'char(36)', 'null' => false, 'default'=>'0'),
				), null, true, false
			);

			craft()->db->createCommand()->addPrimaryKey($tableName, 'id');
			craft()->db->createCommand()->createIndex($tableName, 'id');
			craft()->db->createCommand()->addForeignKey($tableName, 'id', 'elements', 'id', 'CASCADE');

			SproutSeoPlugin::log("Finished creating the {$tableName} table.");
		}
		else
		{
			SproutSeoPlugin::log("The {$tableName} table already exists", LogLevel::Info, true);
		}

		return true;
	}
}
