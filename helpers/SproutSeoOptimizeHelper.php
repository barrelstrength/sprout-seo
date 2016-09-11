<?php
namespace Craft;

class SproutSeoOptimizeHelper
{
	/**
	 * @param $prioritizedMetaTagModel
	 * @param $MetadataGroupModel
	 * @param $globalFallbackMetaTagModel
	 * @param $entryOverrideMetaTagModel
	 *
	 * @return string
	 */
	public static function prepareAppendedSiteName(
		$prioritizedMetaTagModel,
		$MetadataGroupMetaTagModel,
		$globalFallbackMetaTagModel,
		$entryOverrideMetaTagModel
	)
	{
		// Does a selected Metadata Group override the Global Fallback appendSiteName value?
		$appendSiteName = is_null($MetadataGroupMetaTagModel->appendSiteName)
			? $globalFallbackMetaTagModel->appendSiteName
			: $MetadataGroupMetaTagModel->appendSiteName;

		$appendSiteName = is_null(
			$entryOverrideMetaTagModel->title) ?
			$appendSiteName :
			$entryOverrideMetaTagModel->title;

		if ($appendSiteName)
		{
			$divider = craft()->plugins->getPlugin('sproutseo')->getSettings()->seoDivider;

			return $prioritizedMetaTagModel->title . " " . $divider . " " . craft()->getInfo('siteName');
		}

		return $prioritizedMetaTagModel->title;
	}

	/**
	 * Set the default canonical URL to be the current URL
	 */
	public static function prepareCanonical()
	{
		return UrlHelper::getSiteUrl(craft()->request->path);
	}

	/**
	 * Set the geo 'position' attribute based on the 'latitude' and 'longitude'
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
	public static function getRobotsMetaValue($robots = null)
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
	public static function prepRobotsForSettings($robotsString)
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
	 * @param $prioritizedMetaTagModel
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
	 * Return the URL from our Globals settings if it exists. Otherwise return the Craft siteUrl value.
	 *
	 * @param null $url
	 *
	 * @return null|string
	 */
	public static function getGlobalSiteUrl($url = null)
	{
		if (!$url)
		{
			return UrlHelper::getSiteUrl();
		}

		return $url;
	}
}