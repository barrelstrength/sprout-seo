<?php
namespace Craft;

class SproutSeo_SettingsService extends BaseApplicationComponent
{
	/**
	 * @param $settings
	 * @return bool
	 */
	public function saveSettings($settings)
	{
		$settings = JsonHelper::encode($settings);

		$affectedRows = craft()->db->createCommand()->update('plugins', array(
			'settings' => $settings
		), array(
			'class' => 'SproutSeo'
		));

		return (bool) $affectedRows;
	}

}
