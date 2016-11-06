<?php
namespace Craft;
/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160901_000002_sproutSeo_addDefaultGlobalMetadata extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName = 'sproutseo_metadata_globals';

		// Find all currents globals
		$global = craft()->db->createCommand()
			->select('*')
			->from($tableName)
			->queryRow();

		if (!$global)
		{
			$locale = craft()->i18n->getLocaleById(craft()->language);

			craft()->db->createCommand()->insert($tableName, array(
				'locale'    => $locale,
				'identity'  => null,
				'ownership' => null,
				'contacts'  => null,
				'social'    => null,
				'meta'      => null,
				'robots'    => null,
				'settings'  => null,
			));

			SproutSeoPlugin::log("Added default value to globals", LogLevel::Info, true);
		}

		return true;
	}
}