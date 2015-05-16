<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m150423_000000_sproutSeo_addLocaleToOverrides extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		// specify columns and AttributeType
		$locale = array (
			'locale' => ColumnType::Char
		);

		SproutSeoPlugin::log('Dropping `entryId` index on the sproutseo_overrides table...', LogLevel::Info, true);

		MigrationHelper::dropIndexIfExists('sproutseo_overrides', 'entryId');

		SproutSeoPlugin::log('Done dropping `entryId` index on the sproutseo_overrides table.', LogLevel::Info, true);

		$this->_addColumnsAfter($locale, 'entryId');

		// return true and let craft know its done
		return true;
	}

	private function _addColumnsAfter($newColumns, $afterColumnHandle)
	{
		// specify the table name here
		$tableName = 'sproutseo_overrides';

		// this is a foreach loop, enough said
		foreach ($newColumns as $columnName => $columnType)
		{
			// check if the column does NOT exist
			if (!craft()->db->columnExists($tableName, $columnName))
			{
				$this->addColumnAfter($tableName, $columnName, array(
					'column' => $columnType,
					'null'   => false,
					),
					$afterColumnHandle
				);

				// log that we created the new column
				SproutSeoPlugin::log("Created the `$columnName` in the `$tableName` table.", LogLevel::Info, true);

			}

			// if the column already exists in the table
			else {

				// tell craft that we couldn't create the column as it alredy exists.
				SproutSeoPlugin::log("Column `$columnName` already exists in the `$tableName` table.", LogLevel::Info, true);

			}
		}
	}
}
