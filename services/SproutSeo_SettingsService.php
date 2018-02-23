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

		// Check if for some reason structureId is deleted
		if (isset($seoSettings->structureId))
		{
			if (!$seoSettings->structureId || !is_numeric($seoSettings->structureId))
			{
				$structure = sproutSeo()->redirects->createStructureRecord();
				$seoSettings->structureId  = $structure->id;
			}
		}

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

		if (isset($settings["localeIdOverride"]))
		{
			$seoSettings->localeIdOverride = isset($settings["localeIdOverride"]) ?
				$settings["localeIdOverride"] :
				$seoSettings->localeIdOverride;
		}

		if (isset($settings['toggleLocaleOverride']) && !$settings['toggleLocaleOverride'])
		{
			$seoSettings->localeIdOverride = null;
		}

		if (isset($settings["displayFieldHandles"]))
		{
			$seoSettings->displayFieldHandles = isset($settings["displayFieldHandles"]) ?
				$settings["displayFieldHandles"] :
				$seoSettings->displayFieldHandles;
		}

		if (isset($settings["enableMetaDetailsFields"]))
		{
			$seoSettings->enableMetaDetailsFields = isset($settings["enableMetaDetailsFields"]) ?
				$settings["enableMetaDetailsFields"] :
				$seoSettings->enableMetaDetailsFields;
		}

		if (isset($settings["enableCustomSections"]))
		{
			$seoSettings->enableCustomSections = isset($settings["enableCustomSections"]) ?
				$settings["enableCustomSections"] :
				$seoSettings->enableCustomSections;
		}

		if (isset($settings["enableMetadataRendering"]))
		{
			$seoSettings->enableMetadataRendering = isset($settings["enableMetadataRendering"]) ?
				$settings["enableMetadataRendering"] :
				$seoSettings->enableMetadataRendering;
		}

		if (isset($settings['toggleMetadataVariable']) and isset($settings["metadataVariable"]))
		{
			if (isset($settings['toggleMetadataVariable']) and $settings['toggleMetadataVariable'] == 0)
			{
				$seoSettings->metadataVariable = null;
			}

			if (isset($settings['toggleMetadataVariable']) and $settings['toggleMetadataVariable'] == 1)
			{
				$seoSettings->metadataVariable = isset($settings["metadataVariable"])
					? $settings["metadataVariable"]
					: $seoSettings->metadataVariable;
			}
		}

		if (isset($settings["totalElementsPerSitemap"]))
		{
			$seoSettings->totalElementsPerSitemap = $settings["totalElementsPerSitemap"];
		}

		if (isset($settings["enableDynamicSitemaps"]))
		{
			$seoSettings->enableDynamicSitemaps = $settings["enableDynamicSitemaps"];
		}

		if (isset($settings["enable404RedirectLog"]))
		{
			$seoSettings->enable404RedirectLog = $settings["enable404RedirectLog"];
		}

		if (isset($settings["total404Redirects"]))
		{
			$seoSettings->total404Redirects = $settings["total404Redirects"];
		}

		$settings = JsonHelper::encode($seoSettings);

		$affectedRows = craft()->db->createCommand()->update('plugins', array(
			'settings' => $settings
		), array(
			'class' => 'SproutSeo'
		));

		return (bool) $affectedRows;
	}

	public function getDescriptionLength()
	{
		$descriptionLength = craft()->config->get('maxMetaDescriptionLength', 'sproutseo');
		$descriptionLength = $descriptionLength > 160 ? $descriptionLength : 160;

		return $descriptionLength;
	}

}
