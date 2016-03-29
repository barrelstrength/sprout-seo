<?php
namespace Craft;
/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160315_030000_sproutSeo_addElementGroupId extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName    = 'sproutseo_sitemap';
		$renameColumn = 'elementGroupId';

		// Find all currents sitemaps
		$sitemaps = craft()->db->createCommand()
			->select('*')
			->from($tableName)
			->where('sectionId IS NOT NULL')
			->queryAll();

		// Set type to currents sitemaps
		foreach ($sitemaps as $key => $sitemap)
		{
			$sitemapRecord       = SproutSeo_SitemapRecord::model()->findByPk($sitemap["id"]);
			$sitemapRecord->type = "sections";

			$sitemapRecord->save(false);
		}

		if (craft()->db->columnExists($tableName, 'sectionId') && !craft()->db->columnExists($tableName, $renameColumn))
		{
			$this->renameColumn($tableName, 'sectionId', $renameColumn);

			SproutSeoPlugin::log("Renamed the column sectionId to `$renameColumn`  in `$tableName` .", LogLevel::Info, true);
		}
		else
		{
			SproutSeoPlugin::log("Column `$renameColumn` already existed in `$tableName`.", LogLevel::Info, true);
		}

		return true;
	}
}