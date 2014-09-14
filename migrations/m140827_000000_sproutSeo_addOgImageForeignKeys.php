<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m140827_000000_sproutSeo_addOgImageForeignKeys extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		// $this->addForeignKey('sproutseo_defaults', 'id', 'assetfiles', 'id');
		// $this->addForeignKey('sproutseo_overrides', 'id', 'assetfiles', 'id');

		SproutSeoPlugin::log('Added FK to assetfiles table.', LogLevel::Info, true);

		// return true and let craft know its done
		return true;
	}
}
