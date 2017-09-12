<?php
namespace Craft;
/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m170912_000000_sproutSeo_renameUrlToUri extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName    = 'sproutseo_metadata_sections';
		$renameColumn = 'uri';

		// Find all currents sections
		$sections = craft()->db->createCommand()
			->select('*')
			->from($tableName)
			->where('isCustom = 0')
			->queryAll();

		// Set null to currents sections
		foreach ($sections as $key => $section)
		{
			craft()->db->createCommand()->update($tableName,
				array('url' => null),
				'id = :id',
				array(':id' => $section['id'])
			);
		}

		if (craft()->db->columnExists($tableName, 'url') && !craft()->db->columnExists($tableName, $renameColumn))
		{
			$this->renameColumn($tableName, 'url', $renameColumn);

			SproutSeoPlugin::log("Renamed the column url to `$renameColumn`  in `$tableName` .", LogLevel::Info, true);
		}
		else
		{
			SproutSeoPlugin::log("Column `$renameColumn` already existed in `$tableName`.", LogLevel::Info, true);
		}

		return true;
	}
}