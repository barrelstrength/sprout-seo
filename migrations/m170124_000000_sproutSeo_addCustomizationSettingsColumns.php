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
			'searchMetaSectionMetadataEnabled'  => $tinyInt,
			'openGraphSectionMetadataEnabled'   => $tinyInt,
			'twitterCardSectionMetadataEnabled' => $tinyInt,
			'geoSectionMetadataEnabled'         => $tinyInt,
			'robotsSectionMetadataEnabled'      => $tinyInt,
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

		$rows = craft()->db->createCommand()
			->select('*')
			->from($tableName)
			->queryAll();

		foreach ($tableNames as $tableName)
		{
			foreach ($rows as $row)
			{
				$customizationSettings = json_decode($row['customizationSettings'], true);

				// updates new columns
				craft()->db->createCommand()->update($tableName, array(
					'searchMetaSectionMetadataEnabled'  => $customizationSettings['searchMetaSectionMetadataEnabled'],
					'openGraphSectionMetadataEnabled'   => $customizationSettings['openGraphSectionMetadataEnabled'],
					'twitterCardSectionMetadataEnabled' => $customizationSettings['twitterCardSectionMetadataEnabled'],
					'geoSectionMetadataEnabled'         => $customizationSettings['geoSectionMetadataEnabled'],
					'robotsSectionMetadataEnabled'      => $customizationSettings['robotsSectionMetadataEnabled']
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
