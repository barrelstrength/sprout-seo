<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m140827_000000_sproutSeo_addColumnsToOverrides extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		// specify columns and AttributeType
		$newColumnsAuthorPublisher = array (
			'author'             => ColumnType::Varchar,
			'publisher'          => ColumnType::Varchar,
		);

		$newColumnsOg = array (
			'ogAuthor'           => ColumnType::Varchar,
			'ogPublisher'        => ColumnType::Varchar,
		);

		$newColumnsTwitter = array (
			'twitterSite'        => ColumnType::Varchar,
			'twitterTitle'       => ColumnType::Varchar,
			'twitterCreator'     => ColumnType::Varchar,
			'twitterDescription' => ColumnType::Varchar,
			'twitterUrl'         => ColumnType::Varchar,
		);

		$this->_addColumnsAfter($newColumnsAuthorPublisher, 'keywords');
		$this->_addColumnsAfter($newColumnsOg, 'ogImage');
		$this->_addColumnsAfter($newColumnsTwitter, 'twitterCard');

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
					'required' => false
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
