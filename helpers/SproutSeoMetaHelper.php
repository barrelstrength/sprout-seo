<?php
namespace Craft;

class SproutSeoMetaHelper
{
	/**
	 *
	 * @param $defaults
	 * @param $globalFallbackMetaModel
	 * @return mixed
	 */
	public static function prepareAppendedSiteName($defaults, $globalFallbackMetaModel)
	{
		// Does a selected Default override the Global Fallback appendSiteName value?
		$appendSiteName = is_null($defaults->appendSiteName)
			? $globalFallbackMetaModel->appendSiteName
			: $defaults->appendSiteName;

		if ($appendSiteName)
		{
			$divider = craft()->plugins->getPlugin('sproutseo')->getSettings()->seoDivider;
			return " " . $divider . " " . craft()->getInfo('siteName');
		}

		return null;
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
	 * @param $prioritizedMetaModel
	 */
	public static function ensureRobotsHasValues($prioritizedMetaModel)
	{
		if (count($prioritizedMetaModel->robots) == 0)
		{
			// If no values are set, we set this to empty which triggers
			// all positive values to be output.  Kinda lame.
			return array('empty');
		}

		return $prioritizedMetaModel->robots;
	}

	public static function prepRobotsForDb($robotsArray)
	{
		return StringHelper::arrayToString($robotsArray);
	}

	public static function prepRobotsForSettings($robotsString)
	{
		return ArrayHelper::stringToArray($robotsString);
	}

	/**
	 * @todo - can probably improve how images are being handled here
	 * @param $prioritizedMetaModel
	 * @throws \Exception
	 */
	public static function prepareAssetUrls(SproutSeo_MetaModel &$model)
	{
		// Modify our Assets to reference their URLs
		if (!empty($model->ogImage))
		{
			// If ogImage starts with "http", roll with it
			// If not, then process what we have to try to extract the URL
			if (substr($model->ogImage, 0, 4) !== "http")
			{
				if (!is_numeric($model->ogImage))
				{
					throw new \Exception('Open Graph Image override value "' . $model->ogImage . '" must be an absolute path.');
				}

				$ogImage = craft()->elements->getElementById($model->ogImage);

				$imageUrl = (string)($ogImage->url);

				if (!empty($ogImage))
				{
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
						$model->ogImageSecure = UrlHelper::getSiteUrl($ogImage->url, null, "https");
					}
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
					throw new \Exception('Twitter Image override value "' . $model->twitterImage . '" must be an	absolute path.');
				}

				$twitterImage = craft()->elements->getElementById($model->twitterImage);

				$imageUrl = (string)($twitterImage->url);

				if (!empty($twitterImage))
				{
					// check to see if Asset already has full Site Url in folder Url
					if (strpos($imageUrl, "http") !== false)
					{
						$model->twitterImage = $twitterImage->url;
					}
					else
					{
						$model->twitterImage = UrlHelper::getSiteUrl($twitterImage->url);
					}
				}
			}
		}
	}
}