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

		$columnsAfterId = array(
			'type'                  => $varchar,
			'enabled'               => array(
				'column'   => ColumnType::TinyInt,
				'required' => true,
				'default'  => 0,
				'length'   => 1,
				'unsigned' => true
			),
			'isCustom'              => array(
				'column'   => ColumnType::TinyInt,
				'required' => true,
				'default'  => 0,
				'length'   => 1,
				'unsigned' => true
			),
			'urlEnabledSectionId'   => array(
				'column'   => ColumnType::Int,
				'required' => false,
				'default'  => null,
				'length'   => 10
			)
		);

		$columnsAfterHandle = array(
			'customizationSettings' => $varchar,
			'schemaOverrideTypeId'  => $varchar,
			'schemaTypeId'          => $varchar,
			'optimizedKeywords'     => $varchar,
			'optimizedImage'        => $varchar,
			'optimizedDescription'  => $varchar,
			'optimizedTitle'        => $varchar,
			'changeFrequency'       => array(
				'column'    => ColumnType::Varchar,
				'required'  => true,
				'default'   => 'weekly',
				'maxLength' => 7
			),
			'priority'              => array(
				'column'   => 'decimal(11,1)',
				'required' => true,
				'default'  => '0.0'
			),
			'url'                   => $varchar
		);

		if (craft()->db->tableExists($tableName))
		{
			foreach ($columnsAfterId as $columnName => $type)
			{
				if (!craft()->db->columnExists($tableName, $columnName))
				{
					$this->addColumnAfter($tableName, $columnName, $type, 'id');

					SproutSeoPlugin::log("Created column `$columnName` in `$newTableName` .", LogLevel::Info, true);
				}
			}

			foreach ($columnsAfterHandle as $columnName => $type)
			{
				if (!craft()->db->columnExists($tableName, $columnName))
				{
					$this->addColumnAfter($tableName, $columnName, $type, 'handle');

					SproutSeoPlugin::log("Created column `$columnName` in `$newTableName` .", LogLevel::Info, true);
				}
			}

			if (!craft()->db->columnExists($tableName, 'ogTransform'))
			{
				$this->addColumnAfter($tableName, 'ogTransform', $varchar, 'ogImage');

				SproutSeoPlugin::log("Created column ogTransform in `$newTableName` .", LogLevel::Info, true);
			}

			if (!craft()->db->columnExists($tableName, 'twitterTransform'))
			{
				$this->addColumnAfter($tableName, 'twitterTransform', $varchar, 'twitterImage');

				SproutSeoPlugin::log("Created column twitterTransform in `$newTableName` .", LogLevel::Info, true);
			}

			$columnToRename = 'ogTitle';
			if (craft()->db->columnExists($tableName, $columnToRename))
			{
				$this->alterColumn($tableName, $columnToRename, $varchar, $columnToRename, 'ogUrl');
			}

			$columnToRename = 'ogDescription';
			if (craft()->db->columnExists($tableName, $columnToRename))
			{
				$this->alterColumn($tableName, $columnToRename, $varchar, $columnToRename, 'ogTitle');
			}

			$columnToRename = 'ogSiteName';
			if (craft()->db->columnExists($tableName, $columnToRename))
			{
				$this->alterColumn($tableName, $columnToRename, $varchar, $columnToRename, 'ogUrl');
			}

			$columnToRename = 'twitterUrl';
			if (craft()->db->columnExists($tableName, $columnToRename))
			{
				$this->alterColumn($tableName, $columnToRename, $varchar, $columnToRename, 'twitterCard');
			}

			$columnToRename = 'twitterCreator';
			if (craft()->db->columnExists($tableName, $columnToRename))
			{
				$this->alterColumn($tableName, $columnToRename, $varchar, $columnToRename, 'twitterImage');
			}

			$columnToRename = 'appendSiteName';
			if (craft()->db->columnExists($tableName, $columnToRename))
			{
				$this->alterColumn($tableName, $columnToRename, $varchar, 'appendTitleValue', 'title');
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