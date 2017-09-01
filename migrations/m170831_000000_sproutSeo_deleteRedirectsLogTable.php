<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m170831_000000_sproutSeo_deleteRedirectsLogTable extends BaseMigration
{
	/**
	 * @return bool
	 */
	public function safeUp()
	{
		$tableName = 'sproutseo_redirects_log';

		craft()->db->createCommand()->dropTableIfExists($tableName);

		$row = craft()->db->createCommand()->select('settings')
			->from('plugins')
			->where('class=:class', array(':class' => 'SproutSeo'))
			->queryRow();

		$settings = $row['settings'];

		$settings = json_decode($settings, true);

		$settings['enable404RedirectLog'] = 1;
		$settings['total404Redirects']    = 1000;

		$settingsJson = JsonHelper::encode($settings);

		craft()->db->createCommand()->update('plugins', array(
			'settings' => $settingsJson
		),
			'class=:class', array(':class' => 'SproutSeo')
		);

		return true;
	}
}
