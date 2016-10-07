<?php
namespace Craft;

class SproutSeoOptimizeHelper
{
	/**
	 * Return the URL from our Globals settings if it exists. Otherwise return the Craft siteUrl value.
	 *
	 * @param null $url
	 *
	 * @return null|string
	 */
	public static function getGlobalMetadataSiteUrl($url = null)
	{
		if (!$url)
		{
			return UrlHelper::getSiteUrl();
		}

		return $url;
	}

	/**
	 * Set the default canonical URL to be the current URL
	 *
	 * @return string
	 */
	public static function prepareCanonical()
	{
		return UrlHelper::getSiteUrl(craft()->request->path);
	}

	/**
	 * Set the geo 'position' attribute based on the 'latitude' and 'longitude'
	 *
	 * @param $model
	 *
	 * @return string
	 */
	public static function prepareGeoPosition($model)
	{
		if ($model->latitude && $model->longitude)
		{
			return $model->latitude . ";" . $model->longitude;
		}

		return $model->position;
	}

	/**
	 * Return a comma delimited string of robots meta settings
	 *
	 * @param null $robots
	 *
	 * @return null|string
	 */
	public static function prepareRobotsMetadataValue($robots = null)
	{
		if (!isset($robots))
		{
			return null;
		}

		if (is_string($robots))
		{
			return $robots;
		}

		$robotsMetaValue = '';

		foreach ($robots as $key => $value)
		{
			if ($value == '')
			{
				continue;
			}

			if ($robotsMetaValue == '')
			{
				$robotsMetaValue .= $key;
			}
			else
			{
				$robotsMetaValue .= ',' . $key;
			}
		}

		return $robotsMetaValue;
	}

	/**
	 * Return an array of all robots settings set to their boolean value of on or off
	 *
	 * @param $robotsString
	 *
	 * @return array
	 */
	public static function prepareRobotsMetadataForSettings($robotsString)
	{
		$robotsArray = explode(",", $robotsString);

		$robotsSettings = array();

		foreach ($robotsArray as $key => $value)
		{
			$robotsSettings[$value] = 1;
		}

		$robots = array(
			'noindex'      => 0,
			'nofollow'     => 0,
			'noarchive'    => 0,
			'noimageindex' => 0,
			'noodp'        => 0,
			'noydir'       => 0,
		);

		foreach ($robots as $key => $value)
		{
			if (isset($robotsSettings[$key]))
			{
				$robots[$key] = 1;
			}
		}

		return $robots;
	}

	/**
	 * @todo - improve how images are being handled here
	 *
	 * @param SproutSeo_MetadataModel $model
	 *
	 * @throws \Exception
	 */
	public static function prepareAssetUrls(SproutSeo_MetadataModel &$model)
	{
		// If a code override for ogImageSecure is provided, make sure it's an absolute URL
		if (!empty($model->ogImageSecure))
		{
			if (substr($model->ogImageSecure, 0, 5) !== "https")
			{
				throw new \Exception('Open Graph Secure Image override value "' . $model->ogImageSecure . '" must be a secure, absolute url.');
			}
		}

		// Modify our Assets to reference their URLs
		if (!empty($model->ogImage))
		{
			// If ogImage starts with "http", roll with it
			// If not, then process what we have to try to extract the URL
			if (substr($model->ogImage, 0, 4) !== "http")
			{
				if (!is_numeric($model->ogImage))
				{
					throw new \Exception('Open Graph Image override value "' . $model->ogImage . '" must be an absolute url.');
				}

				$ogImage = craft()->elements->getElementById($model->ogImage);

				if (!empty($ogImage))
				{
					$imageUrl = (string) ($ogImage->url);
					// check to see if Asset already has full Site Url in folder Url
					if (strpos($imageUrl, "http") !== false)
					{
						$model->ogImage = $ogImage->url;
					}
					else
					{
						$model->ogImage = UrlHelper::getSiteUrl($ogImage->url);
					}

					$model->ogImageWidth  = $ogImage->width;
					$model->ogImageHeight = $ogImage->height;
					$model->ogImageType   = $ogImage->mimeType;

					if (craft()->request->isSecureConnection())
					{
						$secureUrl            = preg_replace("/^http:/i", "https:", $ogImage->url);
						$model->ogImageSecure = $secureUrl;
					}
				}
				else
				{
					// If our selected asset was deleted, make sure it is null
					$model->ogImage = null;
				}
			}
		}

		if (!empty($model->twitterImage))
		{
			// If twitterImage starts with "http", roll with it
			// If not, then process what we have to try to extract the URL
			if (substr($model->twitterImage, 0, 4) !== "http")
			{
				if (!is_numeric($model->twitterImage))
				{
					throw new \Exception('Twitter Image override value "' . $model->twitterImage . '" must be an	absolute url.');
				}

				$twitterImage = craft()->elements->getElementById($model->twitterImage);

				if (!empty($twitterImage))
				{
					$imageUrl = (string) ($twitterImage->url);
					// check to se	e if Asset already has full Site Url in folder Url
					if (strpos($imageUrl, "http") !== false)
					{
						$model->twitterImage = $twitterImage->url;
					}
					else
					{
						$model->twitterImage = UrlHelper::getSiteUrl($twitterImage->url);
					}
				}
				else
				{
					// If our selected asset was deleted, make sure it is null
					$model->twitterImage = null;
				}
			}
		}

		if (!empty($model->optimizedImage))
		{
			// If twitterImage starts with "http", roll with it
			// If not, then process what we have to try to extract the URL
			if (substr($model->optimizedImage, 0, 4) !== "http")
			{
				if (!is_numeric($model->optimizedImage))
				{
					throw new \Exception('Meta Image override value "' . $model->optimizedImage . '" must be an	absolute url.');
				}

				$optimizedImage = craft()->elements->getElementById($model->optimizedImage);

				if (!empty($optimizedImage))
				{
					$imageUrl = (string) ($optimizedImage->url);
					// check to se	e if Asset already has full Site Url in folder Url
					if (strpos($imageUrl, "http") !== false)
					{
						$model->optimizedImage = $optimizedImage->url;
					}
					else
					{
						$model->optimizedImage = UrlHelper::getSiteUrl($optimizedImage->url);
					}
				}
				else
				{
					// If our selected asset was deleted, make sure it is null
					$model->optimizedImage = null;
				}
			}
		}
	}

	/**
	 * @param $id
	 *
	 * @return mixed|null|string
	 * @throws \Exception
	 */
	public static function getAssetUrl($id)
	{
		$url = null;

		// If not, then process what we have to try to extract the URL
		if (substr($id, 0, 4) !== "http")
		{
			if (!is_numeric($id))
			{
				throw new \Exception('Meta Image override value "' . $id . '" must be an	absolute url.');
			}

			$asset = craft()->elements->getElementById($id);

			if (!empty($asset))
			{
				$imageUrl = (string) ($asset->url);

				// check to see if Asset already has full Site Url in folder Url
				if (strpos($imageUrl, "http") !== false)
				{
					$url = $asset->url;
				}
				else
				{
					$url = UrlHelper::getSiteUrl($asset->url);
				}
			}
			else
			{
				// If our selected asset was deleted, make sure it is null
				$url = null;
			}
		}

		return $url;
	}

	/**
	 * Check our Social Profile settings for a Twitter profile.
	 * Return the first Twitter profile as an @profileName
	 *
	 * @param $socialProfiles
	 *
	 * @return null|string
	 */
	public static function getTwitterProfileName($socialProfiles = array())
	{
		if (!isset($socialProfiles))
		{
			return null;
		}

		$twitterProfileName = null;

		foreach ($socialProfiles as $profile)
		{
			$socialProfileNameFromPost     = isset($profile[0]) ? $profile[0] : null;
			$socialProfileNameFromSettings = isset($profile['profileName']) ? $profile['profileName'] : null;

			// Support syntax for both POST data being saved and previous saved social settings
			if ($socialProfileNameFromPost == 'Twitter' or $socialProfileNameFromSettings == 'Twitter')
			{
				$twitterUrlFromPost = isset($socialProfileNameFromPost) ? $profile[1] : null;
				$twitterUrl         = isset($socialProfileNameFromSettings) ? $profile['url'] : $twitterUrlFromPost;

				$twitterProfileName = '@' . substr($twitterUrl, strrpos($twitterUrl, '/') + 1);

				break;
			}
		}

		return $twitterProfileName;
	}

	/**
	 * @param $socials
	 *
	 * @return null
	 */
	public static function getGooglePlusPage()
	{
		$googlePlusUrl = null;

		$globals = sproutSeo()->globalMetadata->getGlobalMetadata();

		if (count($globals['social']))
		{
			foreach ($globals['social'] as $key => $socialProfile)
			{
				if ($socialProfile['profileName'] == "Google+")
				{
					$googlePlusUrl = $socialProfile['url'];
				}
			}
		}

		return $googlePlusUrl;
	}

	/**
	 * @param $prioritizedMetadataModel
	 * @param $sectionMetadataModel
	 * @param $globalMetadataModel
	 *
	 * @return string
	 */
	public static function prepareAppendedTitleValue(
		$prioritizedMetadataModel,
		$sectionMetadataModel,
		$globalMetadataModel
	)
	{
		$globals  = sproutSeo()->globalMetadata->getGlobalMetadata();
		$settings = $globals->settings;

		// @todo - appendTitleValue should be saved/updated with globals so we don't have to make an extra query here
		$globalAppendTitleValue = $settings['appendTitleValue'];
		$seoDivider             = $settings['seoDivider'];

		// @todo - should this logic happen while populating the $globalMetadataModel?
		switch ($globalAppendTitleValue)
		{
			case 'custom':
				$globalAppendTitleValue = $globalAppendTitleValue;
				break;

			case 'sitename':
				$globalAppendTitleValue = craft()->getInfo('siteName');
				break;

			default:
				$globalAppendTitleValue = null;
				break;
		}

		// @todo - can probably make logic more concise
		if ($sectionMetadataModel->appendTitleValue != '')
		{
			$appendTitleValue = $sectionMetadataModel->appendTitleValue;
		}
		else
		{
			$appendTitleValue = $globalAppendTitleValue;
		}

		if ($appendTitleValue)
		{
			// Add support for using {divider} and {siteName} in the Section Metadata 'Append Meta Title' setting
			$appendTitleValue = craft()->templates->renderObjectTemplate($appendTitleValue, array(
				'siteName' => craft()->getInfo('siteName'),
				'divider'  => $seoDivider
			));

			return $prioritizedMetadataModel->title . " " . $seoDivider . " " . $appendTitleValue;
		}

		return $prioritizedMetadataModel->title;
	}

	/**
	 * @param $model
	 *
	 * @return mixed
	 */
	public static function updateOptimizedAndAdvancedMetaValues($model)
	{
		$globals        = sproutSeo()->globalMetadata->getGlobalMetadata();
		$globalSettings = $globals->settings;

		// Prepare our optimized variables
		// -------------------------------------------------------------
		$optimizedTitle       = (!empty($model->optimizedTitle) ? $model->optimizedTitle : null);
		$optimizedDescription = (!empty($model->optimizedDescription) ? $model->optimizedDescription : null);

		// Make our images single IDs instead of an array
		$optimizedImage = (!empty($model->optimizedImage) and is_array($model->optimizedImage)) ? $model['optimizedImage'][0] : $model->optimizedImage;
		$ogImage        = (!empty($model->ogImage) and is_array($model->ogImage)) ? $model['ogImage'][0] : $model->ogImage;
		$twitterImage   = (!empty($model->twitterImage) and is_array($model->twitterImage)) ? $model['twitterImage'][0] : $model->twitterImage;

		$model['optimizedImage'] = $optimizedImage;
		$model['ogImage']        = $ogImage;
		$model['twitterImage']   = $twitterImage;

		// Set null values for any Advanced SEO Optimization
		// override fields whose blocks have been disabled
		// -------------------------------------------------------------
		$customizationSettings = JsonHelper::decode($model->customizationSettings);

		if (!$customizationSettings['searchMetaSectionMetadataEnabled'])
		{
			foreach ($model['searchMeta'] as $attribute => $value)
			{
				$model->{$attribute} = null;
			}
		}

		if (!$customizationSettings['openGraphSectionMetadataEnabled'])
		{
			foreach ($model['openGraphMeta'] as $attribute => $value)
			{
				$model->{$attribute} = null;
			}
		}

		if (!$customizationSettings['twitterCardSectionMetadataEnabled'])
		{
			foreach ($model['twitterCardsMeta'] as $attribute => $value)
			{
				$model->{$attribute} = null;
			}
		}

		if (!$customizationSettings['geoSectionMetadataEnabled'])
		{
			foreach ($model['geographicMeta'] as $attribute => $value)
			{
				$model->{$attribute} = null;
			}
		}

		if (!$customizationSettings['robotsSectionMetadataEnabled'])
		{
			foreach ($model['robotsMeta'] as $attribute => $value)
			{
				$model->{$attribute} = null;
			}
		}

		// Set any values that don't yet exist to the optimized values
		// -------------------------------------------------------------
		$model->title        = !is_null($model->title) ? $model->title : $optimizedTitle;
		$model->ogTitle      = !is_null($model->ogTitle) ? $model->ogTitle : $optimizedTitle;
		$model->twitterTitle = !is_null($model->twitterTitle) ? $model->twitterTitle : $optimizedTitle;

		$model->description        = !is_null($model->description) ? $model->description : $optimizedDescription;
		$model->ogDescription      = !is_null($model->ogDescription) ? $model->ogDescription : $optimizedDescription;
		$model->twitterDescription = !is_null($model->twitterDescription) ? $model->twitterDescription : $optimizedDescription;

		$model->ogImage      = !is_null($model->ogImage) ? $model->ogImage : $optimizedImage;
		$model->twitterImage = !is_null($model->twitterImage) ? $model->twitterImage : $optimizedImage;

		$model->ogType      = !is_null($model->ogType) ? $model->ogType : $globalSettings['defaultOgType'];
		$model->twitterCard = !is_null($model->twitterCard) ? $model->twitterCard : $globalSettings['defaultTwitterCard'];

		return $model;
	}

	/**
	 * Prepare the default field type settings for the Section Metadata context.
	 *
	 * Display all of our fields manually for the Section Metadatas
	 *
	 * @return array
	 */
	public static function getDefaultFieldTypeSettings()
	{
		return array(
			'optimizedTitleField'       => 'manually',
			'optimizedDescriptionField' => 'manually',
			'optimizedImageField'       => 'manually',
			'optimizedKeywordsField'    => 'manually',
			'showMainEntity'            => true,
			'showSearchMeta'            => false,
			'showOpenGraph'             => true,
			'showTwitter'               => true,
			'showGeo'                   => true,
			'showRobots'                => true,
			'displayPreview'            => true,
		);
	}
}
