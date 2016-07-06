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
			'sproutseo_globals',
			'sproutseo_metatagcontent'
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
					->queryRow();

				foreach ($rows as $row)
				{
					craft()->db->createCommand()->update($tableName,
							array('options' => $newOptions),
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