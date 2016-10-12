<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m161012_093430_sproutseo_addAddressToMetadata extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		if (($table = $this->dbConnection->schema->getTable('{{sproutseo_metadata_elements}}')))
		{
			if (($column = $table->getColumn('addressInfoId')) == null)
			{
				$definition = array(
					AttributeType::Number,
					'column'   => ColumnType::Int,
					'required' => false
				);

				$this->addColumnAfter('sproutseo_metadata_elements', 'addressInfoId', $definition, 'robots');
			}
			else
			{
				Craft::log('Tried to add a `addressInfoId` column to the `sproutseo_metadata_elements` table, but there is already
				one there.', LogLevel::Warning);
			}
		}
		else
		{
			Craft::log('Could not find the `sproutseo_metadata_elements` table.', LogLevel::Error);
		}

		return true;
	}
}
