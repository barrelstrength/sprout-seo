<?php
namespace Craft;
/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m170811_000000_sproutSeo_addCountColumnToRedirectsTable extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName  = 'sproutseo_redirects';
		$columnName = 'count';

		if (!craft()->db->columnExists($tableName, $columnName))
		{

			$this->addColumnAfter($tableName, $columnName,
				array(
					'column'   => ColumnType::Int,
					'required' => true,
					'default'  => 0,
				),
				'regex'
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
