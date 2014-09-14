<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m140427_000000_sproutSeo_replaceSitemapTableSchema extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		
		// The Table you wish to add. 'craft_' prefix will be added automatically.
		$tableName = 'sproutseo_sitemap';
		
		// If our Sitemap table already exists, get rid of it
		if (craft()->db->tableExists($tableName))
		{
			SproutSeoPlugin::log("Dropping the `$tableName` table.", LogLevel::Info, true);

			$this->dropTableIfExists($tableName);

		}

		SproutSeoPlugin::log("Creating the `$tableName` table.", LogLevel::Info, true);

		// Review Column Types in craft/app/enums/ColumnType.php
		$this->createTable($tableName, array(
			'sectionId' => array(
				'column' => ColumnType::Int
			),
			'url' => array(
				'column' => ColumnType::Varchar
			),
			'priority' => array(
				'column' => ColumnType::Decimal, 
				'default' => '0.5', 
				'required'=>true, 
				'maxLength' => 2, 
				'decimals' => 1
			),
			'changeFrequency' => array(
				'column'    => ColumnType::Varchar,
				'default'   => 'weekly', 
				'required'  => true, 
				'maxLength' => 7
			),
			'enabled' => array(
				'column'   => ColumnType::Bool,
				'default'  => false, 
				'required' => true
			),
			'ping' => array(
				'column'   => ColumnType::Bool,
				'default'  => false, 
				'required' => true
			)
		));

		$this->createIndex($tableName, 'sectionId', true);
		$this->addForeignKey($tableName, 'sectionId', 'sections', 'id', 'CASCADE');
		
		return true;
	}
}