<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m170814_000000_sproutSeo_addElementsPerSitemap extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$row = craft()->db->createCommand()->select('settings')
			->from('plugins')
			->where('class=:class', array(':class' => 'SproutSeo'))
			->queryRow();

		$settings = $row['settings'];

		$settings = json_decode($settings, true);

		$settings['totalElementsPerSitemap'] = 500;
		$settings['enableDynamicSitemaps']   = 0;

		$settingsJson = JsonHelper::encode($settings);

		craft()->db->createCommand()->update('plugins', array(
			'settings' => $settingsJson
		),
			'class=:class', array(':class' => 'SproutSeo')
		);

		return true;
	}
}
