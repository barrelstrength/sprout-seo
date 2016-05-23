<?php
namespace Craft;

class SproutSeo_SettingsService extends BaseApplicationComponent
{
	/**
	 * Save the plugin settings to the database
	 *
	 * @param $settings
	 *
	 * @return bool
	 */
	public function saveSettings($settings)
	{
		$plugin      = craft()->plugins->getPlugin('sproutseo');
		$seoSettings = $plugin->getSettings();

		if (isset($settings["pluginNameOverride"]))
		{
			$seoSettings->pluginNameOverride = $settings["pluginNameOverride"] != null ?
				$settings["pluginNameOverride"] :
				$seoSettings->pluginNameOverride;
		}

		if (isset($settings["seoDivider"]))
		{
			$seoSettings->seoDivider = $settings["seoDivider"] != null ?
				$settings["seoDivider"] :
				$seoSettings->seoDivider;
		}

		$settings = JsonHelper::encode($seoSettings);

		$affectedRows = craft()->db->createCommand()->update('plugins', array(
			'settings' => $settings
		), array(
			'class' => 'SproutSeo'
		));

		return (bool) $affectedRows;
	}

}
