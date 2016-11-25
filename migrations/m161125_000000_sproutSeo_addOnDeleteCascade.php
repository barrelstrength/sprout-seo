<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m161125_000000_sproutSeo_addOnDeleteCascade extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$this->addForeignKey('sproutseo_metadata_elements', 'elementId', 'elements', 'id', 'CASCADE');

		return true;
	}
}
