<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m140427_000000_sproutSeo_changeFallbacksToTemplates extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{	
		// The Table you wish to add. 'craft_' prefix will be added automatically.
		$oldTableName = 'sproutseo_fallbacks';
		$newTableName = 'sproutseo_templates';
		
		if (!craft()->db->tableExists($newTableName))
		{
			SproutSeoPlugin::log("New table `$newTableName` doesn't exist.", LogLevel::Info, true);

			if (craft()->db->tableExists($oldTableName))
			{
				SproutSeoPlugin::log("Old table `$oldTableName` does exist.", LogLevel::Info, true);
				SproutSeoPlugin::log("Renaming the `$oldTableName` table.", LogLevel::Info, true);
		
				// Rename table
				$this->renameTable($oldTableName, $newTableName);

				SproutSeoPlugin::log("`$oldTableName` table has been renamed to `$newTableName`.", LogLevel::Info, true);
			}
			
		}
		
		return true;
	}
}