<?php
namespace Craft;
/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160706_000000_sproutSeo_updateDeprecatedTwitterCard extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$tableNames = array(
			'sproutseo_metadatagroups',
			'sproutseo_metadatacontent'
		);

		foreach ($tableNames as $tableName)
		{
			if (craft()->db->tableExists($tableName))
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
			else
			{
				SproutSeoPlugin::log("Table {$tableName} does not exists", LogLevel::Error, true);
			}
		}

		return true;
	}
}