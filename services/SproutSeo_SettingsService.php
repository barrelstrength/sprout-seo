<?php
namespace Craft;

class SproutSeo_SettingsService extends BaseApplicationComponent
{

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
