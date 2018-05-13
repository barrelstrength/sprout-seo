<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\helpers;

use barrelstrength\sproutseo\SproutSeo;
use craft\helpers\UrlHelper;
use Craft;
use yii\base\Exception;
use yii\web\ServerErrorHttpException;
use barrelstrength\sproutseo\models\Metadata;

class SproutSeoOptimizeHelper
{
    /**
     * Return the URL from our Globals settings if it exists. Otherwise return the Craft siteUrl value.
     *
     * @param null $url
     *
     * @return string
     * @throws Exception
     */
    public static function getGlobalMetadataSiteUrl($url = null)
    {
        if (!$url) {
            return UrlHelper::siteUrl();
        }
        // @todo - parseEnvironmentString was removed
        //return Craft::$app->config->parseEnvironmentString ($url);
        return UrlHelper::url($url);
    }

    /**
     * Set the default canonical URL to be the current URL
     *
     * @param $metadataModel
     *
     * @return string
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    public static function prepareCanonical($metadataModel)
    {
        $canonical = UrlHelper::siteUrl(Craft::$app->request->getPathInfo());

        if ($metadataModel->canonical) {
            $canonical = $metadataModel->canonical;
        }

        return $canonical;
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
        if ($model->latitude && $model->longitude) {
            return $model->latitude.';'.$model->longitude;
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
        if (!isset($robots)) {
            return null;
        }

        if (is_string($robots)) {
            return $robots;
        }

        $robotsMetaValue = '';

        foreach ($robots as $key => $value) {
            if ($value == '') {
                continue;
            }

            if ($robotsMetaValue == '') {
                $robotsMetaValue .= $key;
            } else {
                $robotsMetaValue .= ','.$key;
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
        $robotsArray = explode(',', $robotsString);

        $robotsSettings = [];

        foreach ($robotsArray as $key => $value) {
            $robotsSettings[$value] = 1;
        }

        $robots = [
            'noindex' => 0,
            'nofollow' => 0,
            'noarchive' => 0,
            'noimageindex' => 0,
            'noodp' => 0,
            'noydir' => 0,
        ];

        foreach ($robots as $key => $value) {
            if (isset($robotsSettings[$key])) {
                $robots[$key] = 1;
            }
        }

        return $robots;
    }

    /**
     * Prepare Asset URLs for metadata
     *
     * @param Metadata $model
     *
     * @throws \Exception
     */
    public static function prepareAssetUrls(Metadata $model)
    {
        // If a code override for ogImageSecure is provided, make sure it's an absolute URL
        if (!empty($model->ogImageSecure)) {
            if (mb_substr($model->ogImageSecure, 0, 5) !== 'https') {
                throw new Exception('Open Graph Secure Image override value "'.$model->ogImageSecure.'" must be a secure, absolute url.');
            }
        }

        $ogTransform = null;
        $twitterTransform = null;

        // Modify our Assets to reference their URLs
        if (!empty($model->ogImage)) {
            if ($model->ogTransform) {
                $ogTransform = SproutSeoOptimizeHelper::getSelectedTransform($model->ogTransform);
            }

            // If ogImage starts with "http", roll with it
            // If not, then process what we have to try to extract the URL
            if (mb_substr($model->ogImage, 0, 4) !== 'http') {
                if (!is_numeric($model->ogImage)) {
                    throw new Exception('Open Graph Image override value "'.$model->ogImage.'" must be an absolute url.');
                }

                $ogImage = Craft::$app->assets->getAssetById($model->ogImage);
                // Check all getUrl to validate getHasUrls()
                if (isset($ogImage) && $ogImage->getUrl()) {
                    $imageUrl = (string)$ogImage->getUrl();

                    if ($ogTransform) {
                        $imageUrl = (string)$ogImage->getUrl($ogTransform);
                    }
                    // check to see if Asset already has full Site Url in folder Url
                    if (strpos($imageUrl, 'http') !== false) {
                        $model->ogImage = $imageUrl;
                    } else {
                        $model->ogImage = UrlHelper::siteUrl($imageUrl);
                    }

                    $model->ogImageWidth = $ogImage->width;
                    $model->ogImageHeight = $ogImage->height;
                    $model->ogImageType = $ogImage->mimeType;

                    if ($ogTransform) {
                        $model->ogImageWidth = $ogImage->getWidth($ogTransform);
                        $model->ogImageHeight = $ogImage->getHeight($ogTransform);
                    }

                    if (Craft::$app->request->getIsSecureConnection()) {
                        $secureUrl = preg_replace('/^http:/i', 'https:', $model->ogImage);
                        $model->ogImage = $secureUrl;
                        $model->ogImageSecure = $secureUrl;
                    }
                } else {
                    // If our selected asset was deleted, make sure it is null
                    $model->ogImage = null;
                }
            }
        }

        if (!empty($model->twitterImage)) {
            if ($model->twitterTransform) {
                $twitterTransform = SproutSeoOptimizeHelper::getSelectedTransform($model->twitterTransform);
            }

            // If twitterImage starts with "http", roll with it
            // If not, then process what we have to try to extract the URL
            if (mb_substr($model->twitterImage, 0, 4) !== 'http') {
                if (!is_numeric($model->twitterImage)) {
                    throw new Exception('Twitter Image override value "'.$model->twitterImage.'" must be an	absolute url.');
                }

                $twitterImage = Craft::$app->assets->getAssetById($model->twitterImage);

                if (isset($twitterImage) && $twitterImage->getUrl()) {
                    $imageUrl = (string)$twitterImage->getUrl();

                    if ($twitterTransform) {
                        $imageUrl = (string)$twitterImage->getUrl($twitterTransform);
                    }
                    // check to se	e if Asset already has full Site Url in folder Url
                    if (strpos($imageUrl, 'http') !== false) {
                        $model->twitterImage = $imageUrl;
                    } else {
                        $model->twitterImage = UrlHelper::siteUrl($imageUrl);
                    }

                    if (Craft::$app->request->getIsSecureConnection()) {
                        $secureUrl = preg_replace('/^http:/i', 'https:', $model->twitterImage);
                        $model->twitterImage = $secureUrl;
                    }
                } else {
                    // If our selected asset was deleted, make sure it is null
                    $model->twitterImage = null;
                }
            }
        }

        if (!empty($model->optimizedImage)) {
            // If twitterImage starts with "http", roll with it
            // If not, then process what we have to try to extract the URL
            if (mb_substr($model->optimizedImage, 0, 4) !== 'http') {
                if (!is_numeric($model->optimizedImage)) {
                    throw new Exception('Meta Image override value "'.$model->optimizedImage.'" must be an	absolute url.');
                }

                $optimizedImage = Craft::$app->elements->getElementById($model->optimizedImage);

                if (isset($optimizedImage) && $optimizedImage->getUrl()) {
                    $imageUrl = (string)$optimizedImage->getUrl();
                    // check to se	e if Asset already has full Site Url in folder Url
                    if (strpos($imageUrl, 'http') !== false) {
                        $model->optimizedImage = $optimizedImage->url;
                    } else {
                        $model->optimizedImage = UrlHelper::siteUrl($optimizedImage->url);
                    }

                    if (Craft::$app->request->getIsSecureConnection()) {
                        $secureUrl = preg_replace('/^http:/i', 'https:', $model->optimizedImage);
                        $model->optimizedImage = $secureUrl;
                    }
                } else {
                    // If our selected asset was deleted, make sure it is null
                    $model->optimizedImage = null;
                }
            }
        }
    }

    /**
     * @param      $id
     * @param null $transform
     *
     * @return null|string
     * @throws \Exception
     */
    public static function getAssetUrl($id, $transform = null)
    {
        $url = null;

        // If not, then process what we have to try to extract the URL
        if (mb_substr($id, 0, 4) !== 'http') {
            if (!is_numeric($id)) {
                throw new Exception('Meta Image override value "'.$id.'" must be an	absolute url.');
            }

            $asset = Craft::$app->elements->getElementById($id);

            if (!empty($asset)) {
                $transform = SproutSeoOptimizeHelper::getSelectedTransform($transform);

                $imageUrl = Craft::$app->getAssets()->getAssetUrl($asset, $transform);

                // check to see if Asset already has full Site Url in folder Url
                if (strpos($imageUrl, 'http') !== false) {
                    $url = $asset->url;
                } else {
                    $protocol = Craft::$app->request->getIsSecureConnection() ? 'https' : 'http';
                    $url = UrlHelper::urlWithProtocol($imageUrl, $protocol);
                }
            } else {
                // If our selected asset was deleted, make sure it is null
                $url = null;
            }
        }

        return $url;
    }

    /**
     * Return pre-defined transform settings or the selected transform handle
     *
     * @param $transformHandle
     *
     * @return mixed
     */
    public static function getSelectedTransform($transformHandle)
    {
        $defaultTransforms = [
            'sproutSeo-socialSquare' => [
                'mode' => 'crop',
                'width' => 400,
                'height' => 400,
                'quality' => 82,
                'position' => 'center-center'
            ],
            'sproutSeo-ogRectangle' => [
                'mode' => 'crop',
                'width' => 1200,
                'height' => 630,
                'quality' => 82,
                'position' => 'center-center'
            ],
            'sproutSeo-twitterRectangle' => [
                'mode' => 'crop',
                'width' => 1024,
                'height' => 512,
                'quality' => 82,
                'position' => 'center-center'
            ]
        ];

        if (isset($defaultTransforms[$transformHandle])) {
            return $defaultTransforms[$transformHandle];
        }

        return $transformHandle;
    }

    /**
     * Check our Social Profile settings for a Twitter profile.
     * Return the first Twitter profile as an @profileName
     *
     * @param $socialProfiles
     *
     * @return null|string
     */
    public static function getTwitterProfileName($socialProfiles = [])
    {
        if (!isset($socialProfiles)) {
            return null;
        }

        $twitterProfileName = null;

        foreach ($socialProfiles as $profile) {
            $socialProfileNameFromPost = $profile[0] ?? null;
            $socialProfileNameFromSettings = $profile['profileName'] ?? null;

            // Support syntax for both POST data being saved and previous saved social settings
            if ($socialProfileNameFromPost === 'Twitter' or $socialProfileNameFromSettings === 'Twitter') {
                $twitterUrlFromPost = isset($socialProfileNameFromPost) ? $profile[1] : null;
                $twitterUrl = isset($socialProfileNameFromSettings) ? $profile['url'] : $twitterUrlFromPost;

                $twitterProfileName = '@'.mb_substr($twitterUrl, strrpos($twitterUrl, '/') + 1);

                break;
            }
        }

        return $twitterProfileName;
    }

    /**
     * Returns the first Facebook Page found in the Social Profile settings
     *
     * @param $socialProfiles
     *
     * @return null|string
     */
    public static function getFacebookPage($socialProfiles = [])
    {
        if (!isset($socialProfiles)) {
            return null;
        }

        $facebookUrl = null;

        foreach ($socialProfiles as $profile) {
            $socialProfileNameFromPost = $profile[0] ?? null;
            $socialProfileNameFromSettings = $profile['profileName'] ?? null;

            // Support syntax for both POST data being saved and previous saved social settings
            if ($socialProfileNameFromPost === 'Facebook' || $socialProfileNameFromSettings === 'Facebook') {
                $facebookUrlFromPost = isset($socialProfileNameFromPost) ? $profile[1] : null;
                $facebookUrl = isset($socialProfileNameFromSettings) ? $profile['url'] : $facebookUrlFromPost;

                break;
            }
        }

        return $facebookUrl;
    }

    /**
     * Returns the first Google+ Page found in the Social Profile settings
     *
     * @return null|string
     * @throws Exception
     */
    public static function getGooglePlusPage()
    {
        $googlePlusUrl = null;

        $globals = SproutSeo::$app->globalMetadata->getGlobalMetadata();

        if (empty($globals['social'])) {
            return null;
        }

        foreach ($globals['social'] as $key => $socialProfile) {
            if ($socialProfile['profileName'] === 'Google+') {
                // Get our first Google+ URL and bail
                $googlePlusUrl = $socialProfile['url'];
                break;
            }
        }

        return $googlePlusUrl;
    }

    /**
     * @param $prioritizedMetadataModel
     *
     * @return string
     *
     * @throws \RuntimeException
     * @throws Exception
     * @throws ServerErrorHttpException
     */
    public static function prepareAppendedTitleValue(
        $prioritizedMetadataModel
    ) {
        $globals = SproutSeo::$app->globalMetadata->getGlobalMetadata();
        $settings = $globals->settings;

        $globalAppendTitleValue = null;
        $appendTitleValueOnHomepage = $settings['appendTitleValueOnHomepage'];
        $seoDivider = $settings['seoDivider'];
        $appendTitleValue = null;

        if ($appendTitleValueOnHomepage || Craft::$app->request->getPathInfo()) {
            $globalAppendTitleValue = $settings['appendTitleValue'];

            switch ($globalAppendTitleValue) {
                case 'sitename':
                    $globalAppendTitleValue = Craft::$app->getInfo()->name;
                    break;
            }
        }

        if ($prioritizedMetadataModel->appendTitleValue) {
            $appendTitleValue = $prioritizedMetadataModel->appendTitleValue;
        } else {
            $appendTitleValue = $globalAppendTitleValue;
        }

        if ($appendTitleValue) {
            // Add support for using {divider} and {siteName} in the Section Metadata 'Append Meta Title' setting
            $appendTitleValue = Craft::$app->view->renderObjectTemplate($appendTitleValue, [
                'siteName' => Craft::$app->getInfo()->name,
                'divider' => $seoDivider
            ]);

            return $prioritizedMetadataModel->title.' '.$seoDivider.' '.$appendTitleValue;
        }

        return $prioritizedMetadataModel->title;
    }

    /**
     * @param $model
     *
     * @return mixed
     * @throws Exception
     */
    public static function updateOptimizedAndAdvancedMetaValues($model)
    {
        $globals = SproutSeo::$app->globalMetadata->getGlobalMetadata();
        $globalSettings = $globals->settings;

        // Prepare our optimized variables
        // -------------------------------------------------------------
        $optimizedTitle = (!empty($model->optimizedTitle) ? $model->optimizedTitle : null);
        $optimizedDescription = (!empty($model->optimizedDescription) ? $model->optimizedDescription : null);

        // Make our images single IDs instead of an array because when it's called from resaveTask sends an single id
        $optimizedImage = !empty($model->optimizedImage) ? $model->optimizedImage : null;
        $ogImage = !empty($model->ogImage) ? $model->ogImage : $optimizedImage;
        $twitterImage = !empty($model->twitterImage) ? $model->twitterImage : $optimizedImage;

        $model['optimizedImage'] = $optimizedImage;
        $model['ogImage'] = $ogImage;
        $model['twitterImage'] = $twitterImage;

        // Set null values for any Advanced SEO Optimization
        // override fields whose blocks have been disabled

        if (!$model->enableMetaDetailsSearch) {
            foreach ($model['searchMeta'] as $attribute => $value) {
                $model->{$attribute} = null;
            }
        }

        if (!$model->enableMetaDetailsOpenGraph) {
            foreach ($model['openGraphMeta'] as $attribute => $value) {
                $model->{$attribute} = null;
            }
        }

        if (!$model->enableMetaDetailsTwitterCard) {
            foreach ($model['twitterCardsMeta'] as $attribute => $value) {
                $model->{$attribute} = null;
            }
        }

        if (!$model->enableMetaDetailsGeo) {
            foreach ($model['geographicMeta'] as $attribute => $value) {
                $model->{$attribute} = null;
            }
        }

        if (!$model->enableMetaDetailsRobots) {
            foreach ($model['robotsMeta'] as $attribute => $value) {
                $model->{$attribute} = null;
            }
        }

        // Set any values that don't yet exist to the optimized values
        // -------------------------------------------------------------
        $model->title = $model->title !== null ? $model->title : $optimizedTitle;
        $model->ogTitle = $model->ogTitle !== null ? $model->ogTitle : $optimizedTitle;
        $model->twitterTitle = $model->twitterTitle !== null ? $model->twitterTitle : $optimizedTitle;

        $model->description = $model->description !== null ? $model->description : $optimizedDescription;
        $model->ogDescription = $model->ogDescription !== null ? $model->ogDescription : $optimizedDescription;
        $model->twitterDescription = $model->twitterDescription !== null ? $model->twitterDescription : $optimizedDescription;

        $model->ogImage = $model->ogImage !== null ? $model->ogImage : $optimizedImage;
        $model->twitterImage = $model->twitterImage !== null ? $model->twitterImage : $optimizedImage;

        $defaultOgType = $globalSettings['defaultOgType'] ?? null;
        $defaultTwitterCard = $globalSettings['defaultTwitterCard'] ?? null;

        $model->ogType = $model->ogType !== null ? $model->ogType : $defaultOgType;
        $model->twitterCard = $model->twitterCard !== null ? $model->twitterCard : $defaultTwitterCard;

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
        return [
            'optimizedTitleField' => 'manually',
            'optimizedDescriptionField' => 'manually',
            'optimizedImageField' => 'manually',
            'optimizedKeywordsField' => 'manually',
            'showMainEntity' => true,
            'showSearchMeta' => false,
            'showOpenGraph' => true,
            'showTwitter' => true,
            'showGeo' => true,
            'showRobots' => true,
            'displayPreview' => true,
            'editCanonical' => false
        ];
    }

}
