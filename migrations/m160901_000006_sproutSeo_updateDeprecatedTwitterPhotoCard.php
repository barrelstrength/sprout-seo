<?php
namespace Craft;
/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160901_000006_sproutSeo_updateDeprecatedTwitterPhotoCard extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$tableNames = array(
			'sproutseo_metadata_sections',
			'sproutseo_metadata_elements'
		);

		foreach ($tableNames as $tableName)
		{
			// Find all currents globals
			$rows = craft()->db->createCommand()
				->select('id')
				->from($tableName)
				->where('twitterCard =:photo', array(':photo' => 'photo'))
				->queryAll();

			foreach ($rows as $row)
			{
				craft()->db->createCommand()->update($tableName,
					array('twitterCard' => 'summary_large_image'),
					'id = :id',
					array(':id' => $row['id'])
				);

				SproutSeoPlugin::log("Updated deprecated photo card", LogLevel::Info, true);
			}
		}

		return true;
	}
}