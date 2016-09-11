<?php
namespace Craft;

class m160522_000000_sproutSeo_renameOverridesToMetadataContent extends BaseMigration
{
	public function safeup()
	{
		// The Table you wish to add. 'craft_' prefix will be added automatically.
		$oldTableName = 'sproutseo_overrides';
		$newTableName = 'sproutseo_metadatacontent';

		if (!craft()->db->tableExists($newTableName))
		{
			SproutSeoPlugin::log("New table `$newTableName` doesn't exist.", LogLevel::Info, true);

			if (craft()->db->tableExists($oldTableName))
			{
				MigrationHelper::dropIndexIfExists($oldTableName, array('entryId', 'locale'), true);

				SproutSeoPlugin::log("Old table `$oldTableName` does exist.", LogLevel::Info, true);
				SproutSeoPlugin::log("Renaming the `$oldTableName` table.", LogLevel::Info, true);

				// Rename table
				$this->renameTable($oldTableName, $newTableName);

				$this->createIndex($newTableName, 'entryId,locale', true);

				SproutSeoPlugin::log("`$oldTableName` table has been renamed to `$newTableName`.", LogLevel::Info, true);
			}
		}

		return true;
	}
}