<?php
namespace Craft;

class m140624_000000_sproutSeo_renameTemplatestoDefaults extends BaseMigration
{
    public function safeup()
    {
        // The Table you wish to add. 'craft_' prefix will be added automatically.
        $oldTableName = 'sproutseo_templates';
        $newTableName = 'sproutseo_defaults';

        if (!craft()->db->tableExists($newTableName))
        {
            SproutSeoPlugin::log("New table `$newTableName` doesn't exist.", LogLevel::Info, true);

            if (craft()->db->tableExists($oldTableName))
            {
                MigrationHelper::dropIndexIfExists('sproutseo_templates', array('name', 'handle'), true);
                
                SproutSeoPlugin::log("Old table `$oldTableName` does exist.", LogLevel::Info, true);
                SproutSeoPlugin::log("Renaming the `$oldTableName` table.", LogLevel::Info, true);

                // Rename table
                $this->renameTable($oldTableName, $newTableName);

                $this->createIndex('sproutseo_defaults', 'name,handle', true);

                SproutSeoPlugin::log("`$oldTableName` table has been renamed to `$newTableName`.", LogLevel::Info, true);
            }

        }

        return true;
    }
}
