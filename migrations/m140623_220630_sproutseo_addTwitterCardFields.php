<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m140623_220630_sproutseo_addTwitterCardFields extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName = 'sproutseo_templates';

		$columnNames = array(
			'twitterSummaryImageSource'           => AttributeType::String,
			'twitterSummaryLargeImageImageSource' => AttributeType::String,
			'twitterPhotoImageSource'             => AttributeType::String,
			'twitterPlayerImageSource'            => AttributeType::String,
			'twitterPlayer'                       => AttributeType::String,
			'twitterPlayerStream'                 => AttributeType::String,
			'twitterPlayerStreamContentType'      => AttributeType::String,
			'twitterPlayerWidth'                  => AttributeType::String,
			'twitterPlayerHeight'                 => AttributeType::String,
		);

		// if the table exists
		if ($tableName)
		{
			// check for the columns in the table
			foreach ($columnNames as $columnName => $attributeType)
			{
                // check if the column exists in the table
				if ( ($column = $tableName->getColumn($columnName) ) == null)
				{
					// tell craft what we are doing
					Craft::log("Adding `$columnName` column to the `$tableName` table.", LogLevel::Info, true);

					// @TODO fix this so this allows us to create a required
					// field from $columnNames array.
					// add the column after the existing
					$this->addColumnAfter($tableName, $columnName, array(
						$attributeType,
						'required' => false
						),
						'userId'
					);
                    // tell craft we added the column to the table
					Craft::log("Added `$columnName` column to the `$tableName` table.", LogLevel::Info, true);
				}
				else
				{
                    // tell craft that the column already existed
					Craft::log("Tried to add `$columnName` column to the `$tableName` table, but the column is already there.", LogLevel::Warning);
				}
			}
		}

		// table doesn't exist so log it to the control panel
		else
		{
            // tell craft we were unable to find the table
			Craft::log("Could not find an `$tableName` table. Wut?", LogLevel::Error);
		}

		return true;

	}
}
