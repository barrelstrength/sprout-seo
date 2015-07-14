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
			SproutSeoPlugin::log('Creating the sproutseo_redirects table.');

			craft()->db->createCommand()->createTable('sproutseo_redirects', array(
				'id'   			   => array('column' => ColumnType::Int, 'null' => false),
				'oldUrl'       => array('column' => ColumnType::Varchar, 'null' => false),
				'newUrl'       => array('column' => ColumnType::Varchar, 'null' => false),
				'method'       => array('column' => 'int(10)', 'null' => false),
				'regex'        => array('column' => ColumnType::Bool, 'null' => false, 'default'=>0),
				'dateCreated'  => array('column' => ColumnType::DateTime, 'null' => false),
				'dateUpdated'  => array('column' => ColumnType::DateTime, 'null' => false),
				'uid'      		 => array('column' => 'char(36)', 'null' => false, 'default'=>'0'),
				), null, true, false
			);
			craft()->db->createCommand()->addPrimaryKey('sproutseo_redirects', 'id');
			craft()->db->createCommand()->createIndex('sproutseo_redirects', 'id');
			craft()->db->createCommand()->addForeignKey('sproutseo_redirects', 'id', 'elements', 'id', 'CASCADE', 'CASCADE');
			SproutSeoPlugin::log('Finished creating the templatecaches table.');
		}
		else
		{
			SproutSeoPlugin::log("The {$tableName} table already exists", LogLevel::Info, true);
		}

		return true;
	}
}
