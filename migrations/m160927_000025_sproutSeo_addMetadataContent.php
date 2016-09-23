<?php
namespace Craft;
/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160927_000025_sproutSeo_addMetadataContent extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName    = 'sproutseo_overrides';
		$newTableName = 'sproutseo_metadatacontent';

		$varchar = array(
			'column'   => ColumnType::Varchar,
			'required' => false,
			'default'  => null,
		);

		$columns = array(
			'customizationSettings' => $varchar,
			'schemaMap'             => $varchar,
			'optimizedKeywords'     => $varchar,
			'optimizedDescription'  => $varchar,
			'optimizedImage'        => $varchar,
			'optimizedTitle'        => $varchar
		);

		$columnsToRename = array(
			'entryId' => 'elementId'
		);

		if (craft()->db->tableExists($tableName))
		{
			foreach ($columns as $columnName => $type)
			{
				if (!craft()->db->columnExists($tableName, $columnName))
				{
					$this->addColumnAfter($tableName, $columnName, $type, 'title');

					SproutSeoPlugin::log("Created column `$columnName` in `$newTableName` .", LogLevel::Info, true);
				}
			}

			MigrationHelper::dropIndexIfExists($tableName, array('entryId', 'locale'), true);

			foreach ($columnsToRename as $columnName => $newColumn)
			{
				if (craft()->db->columnExists($tableName, $columnName))
				{
					$this->renameColumn($tableName, $columnName, $newColumn);
				}
			}

			// finally rename table
			$this->renameTable($tableName, $newTableName);
			$this->createIndex($newTableName, 'elementId,locale', true);
		}
		else
		{
			SproutSeoPlugin::log("Table {$tableName} does not exists", LogLevel::Error, true);
		}

		return true;
	}
}