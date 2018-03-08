<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m180308_000000_sproutSeo_updateDescriptionColumnType extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{

		$this->alterColumn('sproutseo_metadata_sections', 'optimizedDescription', ColumnType::Text);
		$this->alterColumn('sproutseo_metadata_sections', 'description', ColumnType::Text);
		$this->alterColumn('sproutseo_metadata_sections', 'ogDescription', ColumnType::Text);
		$this->alterColumn('sproutseo_metadata_sections', 'twitterDescription', ColumnType::Text);

		$this->alterColumn('sproutseo_metadata_elements', 'optimizedDescription', ColumnType::Text);
		$this->alterColumn('sproutseo_metadata_elements', 'description', ColumnType::Text);
		$this->alterColumn('sproutseo_metadata_elements', 'ogDescription', ColumnType::Text);
		$this->alterColumn('sproutseo_metadata_elements', 'twitterDescription', ColumnType::Text);

		// return true and let craft know its done
		return true;
	}
}
