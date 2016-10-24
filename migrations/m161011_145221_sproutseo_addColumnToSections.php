<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m161011_145221_sproutseo_addColumnToSections extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName  = 'sproutseo_metadata_sections';
		$columnName = 'addressId';

		if (craft()->db->tableExists($tableName))
		{
			if (!craft()->db->columnExists($tableName, $columnName))
			{
					$definition = array(
						AttributeType::Number,
						'column'   => ColumnType::Int,
						'required' => false
					);

					$this->addColumnAfter($tableName, $columnName, $definition, 'robots');
			}
			else
			{
				SproutSeoPlugin::log("Tried to add a {$columnName} column to the {$tableName} table, but there is already
				one there.", LogLevel::Error, true);
			}
		}
		else
		{
			SproutSeoPlugin::log("Could not find the {$tableName} table", LogLevel::Error, true);
		}

		return true;
	}
}
