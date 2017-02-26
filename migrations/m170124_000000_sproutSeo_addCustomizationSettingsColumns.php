<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m170124_000000_sproutSeo_addCustomizationSettingsColumns extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$tableNames = array(
			'sproutseo_metadata_elements',
			'sproutseo_metadata_sections'
		);

		$tinyInt = array(
			'column'   => ColumnType::TinyInt,
			'required' => false,
			'default'  => 0,
		);

		$columns = array(
			'enableMetaDetailsRobots'      => $tinyInt,
			'enableMetaDetailsGeo'         => $tinyInt,
			'enableMetaDetailsTwitterCard' => $tinyInt,
			'enableMetaDetailsOpenGraph'   => $tinyInt,
			'enableMetaDetailsSearch'      => $tinyInt,
		);

		foreach ($tableNames as $tableName)
		{
			foreach ($columns as $columnName => $type)
			{
				if (!craft()->db->columnExists($tableName, $columnName))
				{
					$this->addColumnAfter($tableName, $columnName, $type, 'schemaOverrideTypeId');

					SproutSeoPlugin::log("Created column `$columnName` in `$tableName` .", LogLevel::Info, true);
				}
			}
		}

		foreach ($tableNames as $tableName)
		{
			$rows = craft()->db->createCommand()
				->select('*')
				->from($tableName)
				->queryAll();

			foreach ($rows as $row)
			{
				$customizationSettings = json_decode($row['customizationSettings'], true);

				// updates new columns
				craft()->db->createCommand()->update($tableName, array(
					'enableMetaDetailsSearch'      => $customizationSettings['searchMetaSectionMetadataEnabled'],
					'enableMetaDetailsOpenGraph'   => $customizationSettings['openGraphSectionMetadataEnabled'],
					'enableMetaDetailsTwitterCard' => $customizationSettings['twitterCardSectionMetadataEnabled'],
					'enableMetaDetailsGeo'         => $customizationSettings['geoSectionMetadataEnabled'],
					'enableMetaDetailsRobots'      => $customizationSettings['robotsSectionMetadataEnabled']
				),
					'id = :id',
					array(':id' => $row['id'])
				);
			}
		}

		// let's remove old column
		$this->dropColumn('sproutseo_metadata_elements', 'customizationSettings');
		$this->dropColumn('sproutseo_metadata_sections', 'customizationSettings');

		return true;
	}
}
