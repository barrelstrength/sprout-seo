<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m150427_000000_sproutSeo_prepareLocalizedFieldSaving extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
        $overridesTable = 'sproutseo_overrides';

        if (!craft()->db->columnExists($overridesTable, 'locale'))
        {
            $this->addColumnAfter($overridesTable, 'locale',
                array(
                    'column' => ColumnType::Locale,
                    'required' => true
                ),
                'entryId'
            );

            SproutSeoPlugin::log("Created the column `locale` in `$overridesTable`.", LogLevel::Info, true);
        }
        else
        {
            SproutSeoPlugin::log("Column `locale` already existed in `$overridesTable`.", LogLevel::Info, true);

        }

        MigrationHelper::dropIndexIfExists($overridesTable, 'entryId');
        
        SproutSeoPlugin::log("Index `entryId` dropped from `$overridesTable`.", LogLevel::Info, true);

        craft()->db->createCommand()
            ->createIndex($overridesTable, 'entryId, locale',
                array(
                    'entryId',
                    'locale',
                ),
                true
            );
        SproutSeoPlugin::log("Composite index `localeAndEntryId` created on `$overridesTable`.", LogLevel::Info, true);

		return true;
	}
}