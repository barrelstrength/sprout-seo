<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\models;


use barrelstrength\sproutseo\helpers\OptimizeHelper;
use barrelstrength\sproutseo\SproutSeo;
use craft\base\Element;
use craft\base\Model;

use barrelstrength\sproutseo\enums\MetadataLevels;

/**
 * Class Metadata
 *
 * @property string uri
 */
class Metadata extends Model
{
    /**
     * @var array
     */
    protected $searchMeta = [];

    /**
     * @var array
     */
    protected $robotsMeta = [];

    /**
     * @var array
     */
    protected $geographicMeta = [];

    /**
     * @var array
     */
    protected $openGraphMeta = [];

    /**
     * @var array
     */
    protected $twitterCardsMeta = [];

    /**
     * @var string
     */
    public $appendTitleValue;

    /**
     * @var int
     */
    public $schemaTypeId;

    /**
     * @var int
     */
    public $schemaOverrideTypeId;

    /**
     * @var string
     */
    public $ogTransform;

    /**
     * @var string
     */
    public $twitterTransform;

    //MetaTags

    /**
     * @var string
     */
    public $optimizedTitle;

    /**
     * @var string
     */
    public $optimizedDescription;

    /**
     * @var int
     */
    public $optimizedImage;

    /**
     * @var string
     */
    public $optimizedKeywords;

    /**
     * @var bool
     */
    public $enableMetaDetailsSearch;

    /**
     * @var bool
     */
    public $enableMetaDetailsOpenGraph;

    /**
     * @var bool
     */
    public $enableMetaDetailsTwitterCard;

    /**
     * @var bool
     */
    public $enableMetaDetailsGeo;

    /**
     * @var bool
     */
    public $enableMetaDetailsRobots;

    //searchMeta

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $keywords;

    //robotsMeta

    /**
     * @var
     */
    public $robots;

    /**
     * @var string
     */
    public $canonical;

    //geographicMeta

    /**
     * @var string
     */
    public $region;

    /**
     * @var string
     */
    public $placename;

    /**
     * @var string
     */
    public $position;

    /**
     * @var string
     */
    public $latitude;

    /**
     * @var string
     */
    public $longitude;

    //openGraphMeta

    /**
     * @var string
     */
    public $ogType;

    /**
     * @var string
     */
    public $ogSiteName;

    /**
     * @var string
     */
    public $ogAuthor;

    /**
     * @var string
     */
    public $ogPublisher;

    /**
     * @var string
     */
    public $ogUrl;

    /**
     * @var string
     */
    public $ogTitle;

    /**
     * @var string
     */
    public $ogDescription;

    /**
     * @var string
     */
    public $ogImage;

    /**
     * @var string
     */
    public $ogImageSecure;

    /**
     * @var int
     */
    public $ogImageWidth;

    /**
     * @var int
     */
    public $ogImageHeight;

    /**
     * @var string
     */
    public $ogImageType;

    /**
     * @var string
     */
    public $ogAudio;

    /**
     * @var string
     */
    public $ogVideo;

    /**
     * @var string
     */
    public $ogLocale;

    /**
     * @var \DateTime
     */
    public $ogDateUpdated;

    /**
     * @var \DateTime
     */
    public $ogDateCreated;

    /**
     * @var \DateTime
     */
    public $ogExpiryDate;

    //twitterCardsMeta

    /**
     * @var string
     */
    public $twitterCard;

    /**
     * @var string
     */
    public $twitterSite;

    /**
     * @var string
     */
    public $twitterCreator;

    /**
     * @var string
     */
    public $twitterUrl;

    /**
     * @var string
     */
    public $twitterTitle;

    /**
     * @var string
     */
    public $twitterDescription;

    /**
     * @var string
     */
    public $twitterImage;

    /**
     * @var string
     */
    public $twitterPlayer;

    /**
     * @var string
     */
    public $twitterPlayerStream;

    /**
     * @var string
     */
    public $twitterPlayerStreamContentType;

    /**
     * @var int
     */
    public $twitterPlayerWidth;

    /**
     * @var int
     */
    public $twitterPlayerHeight;

    /**
     * @var \DateTime
     */
    public $dateCreated;

    /**
     * @var \DateTime
     */
    public $dateUpdated;

    /**
     * @var int
     */
    public $uid;

    /**
     * @todo - Refactor
     *         - Can we remove isNew now and just test for ID?
     *         - Do we need default still?
     *         - Do we need url? Can we just test for URL format?
     *         - Do we need isCustom still? Can we just test for urlEnabledSectionId?
     *         - Clarify what 'type' is.
     *         - Craft3 Notes: The values doesn't matter we need the associative arrays to use less code
     *
     */
    public function init()
    {
        parent::init();

        $this->searchMeta = [
            'title' => $this->title,
            'description' => $this->description,
            'keywords' => $this->keywords,
        ];

        $this->robotsMeta = [
            'robots' => $this->robots,
            'canonical' => $this->canonical,
        ];

        $this->geographicMeta = [
            'region' => $this->region,
            'placename' => $this->placename,
            'position' => $this->position,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];

        $this->openGraphMeta = [
            'ogType' => $this->ogType,
            'ogSiteName' => $this->ogSiteName,
            'ogAuthor' => $this->ogAuthor,
            'ogPublisher' => $this->ogPublisher,
            'ogUrl' => $this->ogUrl,
            'ogTitle' => $this->ogTitle,
            'ogDescription' => $this->ogDescription,
            'ogImage' => $this->ogImage,
            'ogImageSecure' => $this->ogImageSecure,
            'ogImageWidth' => $this->ogImageWidth,
            'ogImageHeight' => $this->ogImageHeight,
            'ogImageType' => $this->ogImageType,
            'ogAudio' => $this->ogAudio,
            'ogVideo' => $this->ogVideo,
            'ogLocale' => $this->ogLocale,
            'ogDateUpdated' => $this->ogDateUpdated,
            'ogDateCreated' => $this->ogDateCreated,
            'ogExpiryDate' => $this->ogExpiryDate,
        ];

        $this->twitterCardsMeta = [
            'twitterCard' => $this->twitterCard,
            'twitterSite' => $this->twitterSite,
            'twitterCreator' => $this->twitterCreator,
            'twitterUrl' => $this->twitterUrl,
            'twitterTitle' => $this->twitterTitle,
            'twitterDescription' => $this->twitterDescription,
            'twitterImage' => $this->twitterImage,
            'twitterPlayer' => $this->twitterPlayer,
            'twitterPlayerStream' => $this->twitterPlayerStream,
            'twitterPlayerStreamContentType' => $this->twitterPlayerStreamContentType,
            'twitterPlayerWidth' => $this->twitterPlayerWidth,
            'twitterPlayerHeight' => $this->twitterPlayerHeight,
        ];
    }

    /**
     * @return array
     * @throws \yii\base\Exception
     */
    public function getMetaTagData()
    {
        $metaTagData = [];

        $metaTagData['search'] = $this->getSearchMetaTagData();
        $metaTagData['robots'] = $this->getRobotsMetaTagData();
        $metaTagData['geo'] = $this->getGeographicMetaTagData();
        $metaTagData['openGraph'] = $this->getOpenGraphMetaTagData();
        $metaTagData['twitterCard'] = $this->getTwitterCardMetaTagData();
        $metaTagData['googlePlus'] = $this->getGooglePlusMetaTagData();

        return $metaTagData;
    }

    /**
     * @param string $type
     * @param array  $overrideInfo
     *
     * @return $this
     * @throws \Exception
     */
    public function setMeta($type = MetadataLevels::GlobalMetadata, array $overrideInfo = [])
    {
        switch ($type) {
            case MetadataLevels::GlobalMetadata:
                $this->setAttributes($this->prepareGlobalMetadata($overrideInfo), false);
                break;

            case MetadataLevels::ElementMetadata:
                $this->setAttributes($this->prepareElementMetadata($overrideInfo), false);
                break;

            case MetadataLevels::CodeMetadata:
                $this->setAttributes($this->prepareCodeMetadata($overrideInfo), false);
                break;
        }

        return $this;
    }

    /**
     * @param $overrideInfo
     *
     * @return mixed
     */
    protected function prepareGlobalMetadata($overrideInfo)
    {
        return $overrideInfo['globals']->meta ?? SproutSeo::$app->optimize->globals->meta;
    }

    /**
     * Get Element Metadata based on an Element ID
     *
     * @param $overrideInfo
     *
     * @return array
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    protected function prepareElementMetadata($overrideInfo)
    {
        if (isset($overrideInfo['metadataField']) && isset($overrideInfo['contextElement'])) {

            /**
             * @var Element $element
             */
            $element = $overrideInfo['contextElement'];
            $site = $element->getSite();

            /**
             * @var Metadata $elementMetadata
             */
            $elementMetadata = $overrideInfo['metadataField'];
            $elementMetadata->ogLocale = $site->language;

            // Default to the current URL, if no overrides exist
            $elementMetadata->canonical = OptimizeHelper::prepareCanonical($elementMetadata);
            $elementMetadata->ogUrl = OptimizeHelper::prepareCanonical($elementMetadata);
            $elementMetadata->twitterUrl = OptimizeHelper::prepareCanonical($elementMetadata);

            return $elementMetadata->getAttributes();
        }

        return [];
    }

    /**
     * Process any Meta Tags provided in via the templates and create a SproutSeo_MetaTagsModel
     *
     * @param $overrideInfo
     *
     * @return array
     */
    protected function prepareCodeMetadata($overrideInfo)
    {
        if (!empty($overrideInfo)) {
            return $overrideInfo;
        }

        return [];
    }

    /**
     * @return array
     */
    protected function getSearchMetaTagData()
    {
        $tagData = [];

        foreach ($this->searchMeta as $key => $value) {
            if ($this->{$key}) {
                // @todo - parseEnvironmentString was removed
                $value = $this->{$key};
                $tagData[$key] = $value;
            }
        }

        return $tagData;
    }

    /**
     * @return array
     */
    protected function getRobotsMetaTagData()
    {
        $tagData = [];

        foreach ($this->robotsMeta as $key => $value) {
            if ($this->{$key}) {
                $value = $this->{$key};

                if ($key == 'robots') {
                    $value = $this->robots;
                }

                $tagData[$key] = $value;
            }
        }

        return $tagData;
    }

    /**
     * @return array
     */
    protected function getGeographicMetaTagData()
    {
        $tagData = [];

        foreach ($this->geographicMeta as $key => $value) {
            if ($key == 'latitude' or $key == 'longitude') {
                break;
            }

            if ($this->{$key}) {
                $value = $this[$key];

                if ($key == 'position') {
                    $value = OptimizeHelper::prepareGeoPosition($this);
                }

                $tagData[$this->getMetaTagName($key)] = $value;
            }
        }

        return $tagData;
    }

    /**
     * @return array
     */
    protected function getOpenGraphMetaTagData()
    {
        $tagData = [];

        foreach ($this->openGraphMeta as $key => $value) {
            if ($this->{$key}) {
                // @todo - parseEnvironmentString was removed
                $value = $this->{$key};
                $tagData[$this->getMetaTagName($key)] = $value;
            }
        }

        return $tagData;
    }

    /**
     * @return array
     */
    protected function getTwitterCardMetaTagData()
    {
        $tagData = [];

        foreach ($this->twitterCardsMeta as $key => $value) {
            if ($this->{$key}) {
                // @todo - parseEnvironmentString was removed
                $value = $this->{$key};
                $tagData[$this->getMetaTagName($key)] = $value;
            }
        }

        return $tagData;
    }

    /**
     * @return null
     * @throws \yii\base\Exception
     */
    public function getGooglePlusMetaTagData()
    {
        return OptimizeHelper::getGooglePlusPage();
    }

    /**
     * @param $handle
     *
     * @return mixed
     */
    protected function getMetaTagName($handle)
    {
        // Map tag names to their handles
        $tagNames = [

            // Geographic
            'region' => 'geo.region',
            'placename' => 'geo.placename',
            'position' => 'geo.position',

            // Open Graph
            'ogType' => 'og:type',
            'ogSiteName' => 'og:site_name',
            'ogPublisher' => 'article:publisher',
            'ogAuthor' => 'og:author',
            'ogUrl' => 'og:url',
            'ogTitle' => 'og:title',
            'ogDescription' => 'og:description',
            'ogImage' => 'og:image',
            'ogImageSecure' => 'og:image:secure_url',
            'ogImageWidth' => 'og:image:width',
            'ogImageHeight' => 'og:image:height',
            'ogImageType' => 'og:image:type',
            'ogAudio' => 'og:audio',
            'ogVideo' => 'og:video',
            'ogLocale' => 'og:locale',
            'ogDateCreated' => 'article:published_time',
            'ogDateUpdated' => 'article:modified_time',
            'ogExpiryDate' => 'article:expiration_time',

            // Twitter Cards
            'twitterCard' => 'twitter:card',
            'twitterSite' => 'twitter:site',
            'twitterCreator' => 'twitter:creator',
            'twitterTitle' => 'twitter:title',
            'twitterDescription' => 'twitter:description',
            'twitterUrl' => 'twitter:url',
            'twitterImage' => 'twitter:image',
            'twitterPlayer' => 'twitter:player',
            'twitterPlayerStream' => 'twitter:player:stream',
            'twitterPlayerStreamContentType' => 'twitter:player:stream:content_type',
            'twitterPlayerWidth' => 'twitter:player:width',
            'twitterPlayerHeight' => 'twitter:player:height',
        ];

        return $tagNames[$handle];
    }

    /**
     * Updates "uri" to starts without a "/"
     *
     * @return bool
     */
    public function beforeValidate()
    {
        $this->uri = SproutSeo::$app->xmlSitemap->removeSlash($this->uri);

        return true;
    }
}
