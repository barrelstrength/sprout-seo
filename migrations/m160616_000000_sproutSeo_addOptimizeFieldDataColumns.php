<?php
namespace Craft;
/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160616_000000_sproutSeo_addOptimizeFieldDataColumns extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName  = 'sproutseo_metatagcontent';
		$columnName = 'elementTitle';

		if (!craft()->db->columnExists($tableName, $columnName))
		{
			$this->addColumnAfter($tableName, $columnName,
				array(
					'column'   => ColumnType::Varchar,
					'required' => false,
					'default'  => null,
				),
				'title'
			);

			SproutSeoPlugin::log("Created the column `$columnName` in `$tableName` .", LogLevel::Info, true);
		}
		else
		{
			SproutSeoPlugin::log("Column `$columnName` already exists in `$tableName`.", LogLevel::Info, true);
		}

		return true;
	}
}
