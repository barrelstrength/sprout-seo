<?php
namespace Craft;
/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160308_000000_sproutSeo_addCategoriesToSitemap extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName  = 'sproutseo_sitemap';
		$columnName = 'categoryGroupId';

		if (!craft()->db->columnExists($tableName, $columnName))
		{
			$this->addColumnAfter($tableName, $columnName,
				array(
					'column'   => ColumnType::Int,
					'required' => false,
					'default'  => null,
				),
				'sectionId'
			);

			SproutSeoPlugin::log("Created the column `locale` in `$tableName`.", LogLevel::Info, true);
		}
		else
		{
			SproutSeoPlugin::log("Column `$columnName` already existed in `$tableName`.", LogLevel::Info, true);
		}

		return true;
	}
}