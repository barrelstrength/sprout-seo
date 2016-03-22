<?php
namespace Craft;
/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160315_020000_sproutSeo_addSitemapTypeColumn extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName    = 'sproutseo_sitemap';
		$columnName   = 'type';

		if (!craft()->db->columnExists($tableName, $columnName))
		{

			$this->addColumnAfter($tableName, $columnName,
				array(
					'column'   => ColumnType::Varchar,
					'required' => false,
					'default'  => null,
				),
				'sectionId'
			);

			SproutSeoPlugin::log("Created the column `$columnName` in `$tableName` .", LogLevel::Info, true);
		}
		else
		{
			SproutSeoPlugin::log("Column `$columnName` already existed in `$tableName`.", LogLevel::Info, true);
		}

		return true;
	}
}
