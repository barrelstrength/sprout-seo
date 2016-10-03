<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160901_000003_sproutSeo_addSectionMetadataTable extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName    = 'sproutseo_defaults';
		$newTableName = 'sproutseo_metadata_sections';

		$varchar = array(
			'column'   => ColumnType::Varchar,
			'required' => false,
			'default'  => null,
		);

		$columns = array(
			'customizationSettings' => $varchar,
			'url' => $varchar,
			'isCustom' => array(
				'column'   => ColumnType::TinyInt,
				'required' => false,
				'default'  => 0
			),
			'schemaMap' => $varchar,
			'enabled' => array(
				'column'   => ColumnType::TinyInt,
				'required' => false,
				'default'  => 0
			),
			'changeFrequency' => array(
				'column'    => ColumnType::Varchar,
				'required'  => false,
				'default'   => 'weekly',
				'maxLength' => 7
			),
			'priority' => array(
				'column'   => 'decimal(12,1)',
				'required' => false,
				'default'  => '0.0'
			),
			'optimizedKeywords' => $varchar,
			'optimizedDescription' => $varchar,
			'optimizedImage' => $varchar,
			'optimizedTitle' => $varchar,
			'type' => $varchar,
			'urlEnabledSectionId' => array(
				'column'   => ColumnType::Int,
				'required' => false,
				'default'  => null
			),
		);

		$columnsToRename = array(
			'appendSiteName' => 'appendTitleValue'
		);

		if (craft()->db->tableExists($tableName))
		{
			foreach ($columns as $columnName => $type)
			{
				if (!craft()->db->columnExists($tableName, $columnName))
				{
					$this->addColumnAfter($tableName, $columnName, $type, 'handle');

					SproutSeoPlugin::log("Created column `$columnName` in `$newTableName` .", LogLevel::Info, true);
				}
			}

			foreach ($columnsToRename as $columnName => $newColumn)
			{
				if (craft()->db->columnExists($tableName, $columnName))
				{
					$this->renameColumn($tableName, $columnName, $newColumn);
				}
			}

			// finally rename table
			MigrationHelper::dropIndexIfExists($tableName, array('name', 'handle'), true);
			$this->renameTable($tableName, $newTableName);
			$this->createIndex($newTableName, 'name,handle', true);
		}
		else
		{
			SproutSeoPlugin::log("Table {$tableName} does not exists", LogLevel::Error, true);
		}

		return true;
	}
}