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
		$tableName   = 'sproutseo_metatagcontent';
		$columnsName = array(
			array('name'=>'elementTitle', 'after'=>'title'),
			array('name'=>'metaImage', 'after'=>'description')
		);

		foreach ($columnsName as $key => $column)
		{
			if (!craft()->db->columnExists($tableName, $column['name']))
			{
				$this->addColumnAfter($tableName, $column['name'],
					array(
						'column'   => ColumnType::Varchar,
						'required' => false,
						'default'  => null,
					),
					$column['after']
				);

				SproutSeoPlugin::log("Created the column {$column['name']} in {$tableName} .", LogLevel::Info, true);
			}
			else
			{
				SproutSeoPlugin::log("Column {$column['name']} already exists in {$tableName}.", LogLevel::Info, true);
			}
		}

		return true;
	}
}
