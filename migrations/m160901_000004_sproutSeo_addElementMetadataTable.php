<?php
namespace Craft;
/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160901_000004_sproutSeo_addElementMetadataTable extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName    = 'sproutseo_overrides';
		$newTableName = 'sproutseo_metadata_elements';

		$varchar = array(
			'column'   => ColumnType::Varchar,
			'required' => false,
			'default'  => null,
		);

		$columns = array(
			'customizationSettings' => $varchar,
			'schemaOverrideTypeId'  => $varchar,
			'schemaTypeId'          => $varchar,
			'optimizedKeywords'     => $varchar,
			'optimizedImage'        => $varchar,
			'optimizedDescription'  => $varchar,
			'optimizedTitle'        => $varchar
		);

		$columnsToRename = array(
			'entryId' => 'elementId'
		);

		foreach ($columns as $columnName => $type)
		{
			if (!craft()->db->columnExists($tableName, $columnName))
			{
				$this->addColumnAfter($tableName, $columnName, $type, 'locale');

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

		$columnsToMove = array(
			'ogTitle' => array(
				'type' => $varchar,
				'after' => 'ogUrl'
			),
			'ogSiteName' => array(
				'type' => $varchar,
				'after' => 'ogUrl'
			),
			'ogDescription' => array(
				'type' => $varchar,
				'after' => 'ogTitle'
			),
			'twitterUrl' => array(
				'type' => $varchar,
				'after' => 'twitterCard'
			),
			'twitterDescription' => array(
				'type' => $varchar,
				'after' => 'twitterTitle'
			),
			'twitterImage' => array(
				'type' => $varchar,
				'after' => 'twitterDescription'
			),
		);

		foreach ($columnsToMove as $columnToRename => $info)
		{
			$this->alterColumn($tableName, $columnToRename, $info['type'], $columnToRename, $info['after']);
		}

		$this->addColumnAfter($tableName, 'ogTransform', $varchar, 'ogImage');
		SproutSeoPlugin::log("Created column ogTransform in `$tableName` .", LogLevel::Info, true);

		$this->addColumnAfter($tableName, 'twitterTransform', $varchar, 'twitterImage');
		SproutSeoPlugin::log("Created column twitterTransform in `$tableName` .", LogLevel::Info, true);

		// Removes publisher and author columns
		$this->dropColumn($tableName, 'publisher');
		$this->dropColumn($tableName, 'author');

		// finally rename table
		$this->renameTable($tableName, $newTableName);
		$this->createIndex($newTableName, 'elementId,locale', true);

		return true;
	}
}