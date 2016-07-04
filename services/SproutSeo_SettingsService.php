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

		if (isset($settings["twitterCard"]))
		{
			$seoSettings->twitterCard = $settings["twitterCard"] != null ?
				$settings["twitterCard"] :
				$seoSettings->twitterCard;
		}

		if (isset($settings["ogType"]))
		{
			$seoSettings->ogType = $settings["ogType"] != null ?
				$settings["ogType"] :
				$seoSettings->ogType;
		}

		if (isset($settings["templateFolder"]))
		{
			$seoSettings->templateFolder = $settings["templateFolder"] != null ?
				$settings["templateFolder"] :
				$seoSettings->templateFolder;
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
