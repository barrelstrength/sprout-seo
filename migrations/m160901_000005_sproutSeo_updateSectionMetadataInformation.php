<?php
namespace Craft;
/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160901_000005_sproutSeo_updateSectionMetadataInformation extends BaseMigration
{
	/**
	 * Let's dance!
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName = "sproutseo_metadata_sections";

		if (craft()->db->tableExists($tableName))
		{
			// Find all Section Metadata Sections and set all the rows as custom pages
			$rows = craft()->db->createCommand()
				->select('id, handle, name')
				->from($tableName)
				->queryAll();

			foreach ($rows as $row)
			{
				// let's validate any possible duplicate handle
				$urlEnabledSectionTypes = sproutSeo()->sectionMetadata->getUrlEnabledSectionTypes();

				foreach ($urlEnabledSectionTypes as $urlEnabledSectionTypeKey => $urlEnabledSectionType)
				{
					foreach ($urlEnabledSectionType->urlEnabledSections as $urlEnabledSectionKey => $urlEnabledSection)
					{
						$sectionMetadata = $urlEnabledSection->sectionMetadata;

						if (isset($sectionMetadata->name))
						{
							if (isset($sectionMetadata->handle))
							{
								$handle = $sectionMetadata->handle;

								if ($row['handle'] == $handle)
								{
									$row['handle'] = "sproutSeo".ucfirst($row['handle']);
									break 2;
								}
							}
						}
					}
				}

				craft()->db->createCommand()->update($tableName,
					array('isCustom' => 1, 'handle' => $row['handle']),
					'id = :id',
					array(':id' => $row['id'])
				);
			}
		}
		else
		{
			SproutSeoPlugin::log("Table {$tableName} does not exists", LogLevel::Error, true);
		}

		return true;
	}
}