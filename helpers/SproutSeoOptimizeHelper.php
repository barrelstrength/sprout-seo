<?php
namespace Craft;

class SproutSeoOptimizeHelper
{
	/**
	 * @param $prioritizedMetaTagModel
	 * @param $metaTagsGroupModel
	 * @param $globalFallbackMetaTagModel
	 * @param $entryOverrideMetaTagModel
	 * @return string
	 */
	public static function prepareAppendedSiteName(
		$prioritizedMetaTagModel,
		$metaTagsGroupMetaTagModel,
		$globalFallbackMetaTagModel,
		$entryOverrideMetaTagModel
	)
	{
		// Does a selected Meta Tag Group override the Global Fallback appendSiteName value?
		$appendSiteName = is_null($metaTagsGroupMetaTagModel->appendSiteName)
			? $globalFallbackMetaTagModel->appendSiteName
			: $metaTagsGroupMetaTagModel->appendSiteName;

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

	public static function prepRobotsAsString($robotsArray)
	{
		return StringHelper::arrayToString($robotsArray);
	}

	public static function prepRobotsForSettings($robotsString)
	{
		return ArrayHelper::stringToArray($robotsString);
	}

	/**
	 * @todo - improve how images are being handled here
	 *
	 * @param $prioritizedMetaTagModel
	 *
	 * @throws \Exception
	 */
	public static function prepareAssetUrls(SproutSeo_MetaTagsModel &$model)
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

		if (!empty($model->metaImage))
		{
			// If twitterImage starts with "http", roll with it
			// If not, then process what we have to try to extract the URL
			if (substr($model->metaImage, 0, 4) !== "http")
			{
				if (!is_numeric($model->metaImage))
				{
					throw new \Exception('Meta Image override value "' . $model->metaImage . '" must be an	absolute url.');
				}

				$metaImage = craft()->elements->getElementById($model->metaImage);

				if (!empty($metaImage))
				{
					$imageUrl = (string) ($metaImage->url);
					// check to se	e if Asset already has full Site Url in folder Url
					if (strpos($imageUrl, "http") !== false)
					{
						$model->metaImage = $metaImage->url;
					}
					else
					{
						$model->metaImage = UrlHelper::getSiteUrl($metaImage->url);
					}
				}
				else
				{
					// If our selected asset was deleted, make sure it is null
					$model->metaImage = null;
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

			$metaImage = craft()->elements->getElementById($id);

			if (!empty($metaImage))
			{
				$imageUrl = (string) ($metaImage->url);
				// check to se	e if Asset already has full Site Url in folder Url
				if (strpos($imageUrl, "http") !== false)
				{
					$url = $metaImage->url;
				}
				else
				{
					$url = UrlHelper::getSiteUrl($metaImage->url);
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
}