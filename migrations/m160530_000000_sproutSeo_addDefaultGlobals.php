<?php
namespace Craft;
/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160530_000000_sproutSeo_addDefaultGlobals extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName = 'sproutseo_globals';

		if (craft()->db->tableExists($tableName))
		{
			// Find all currents globals
			$global = craft()->db->createCommand()
				->select('*')
				->from($tableName)
				->queryRow();

			if (!$global)
			{
				craft()->db->createCommand()->insert($tableName, array(
					'locale'    => null,
					'identy'    => null,
					'ownership' => null,
					'contacts'  => null,
					'social'    => null
				));

				SproutSeoPlugin::log("Added default value to globals", LogLevel::Info, true);
			}
		}

		return true;
	}
}