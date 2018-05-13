<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\models;

use barrelstrength\sproutseo\helpers\SproutSeoOptimizeHelper;
use barrelstrength\sproutseo\SproutSeo;
use craft\base\Model;
use craft\helpers\UrlHelper;
use barrelstrength\sproutseo\enums\MetadataLevels;
use Craft;

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

    //SITEMAP
    /**
     * @var
     */
    public $id;
    public $siteId;
    public $enabledForSite;
    public $sectionMetadataId;
    public $isNew;

    /**
     * @var
     */
    public $default;

    /**
     * @var
     */
    public $name;

    /**
     * @var
     */
    public $handle;

    /**
     * @var
     */
    public $uri;

    /**
     * @var
     */
    public $priority;

    /**
     * @var
     */
    public $changeFrequency;

    /**
     * @var
     */
    public $urlEnabledSectionId;

    /**
     * @var
     */
    public $isCustom;

    /**
     * @var
     */
    public $type;

    /**
     * @var
     */
    public $enabled;

    /**
     * @var
     */
    public $appendTitleValue;

    /**
     * @var
     */
    public $schemaTypeId;

    /**
     * @var
     */
    public $schemaOverrideTypeId;

    /**
     * @var
     */
    public $ogTransform;

    /**
     * @var
     */
    public $twitterTransform;

    //MetaTags

    /**
     * @var
     */
    public $optimizedTitle;

    /**
     * @var
     */
    public $optimizedDescription;

    /**
     * @var
     */
    public $optimizedImage;

    /**
     * @var
     */
    public $optimizedKeywords;

    /**
     * @var
     */
    public $enableMetaDetailsSearch;

    /**
     * @var
     */
    public $enableMetaDetailsOpenGraph;

    /**
     * @var
     */
    public $enableMetaDetailsTwitterCard;

    /**
     * @var
     */
    public $enableMetaDetailsGeo;

    /**
     * @var
     */
    public $enableMetaDetailsRobots;

    //searchMeta

    /**
     * @var
     */
    public $title;

    /**
     * @var
     */
    public $description;

    /**
     * @var
     */
    public $keywords;

    //robotsMeta

    /**
     * @var
     */
    public $robots;

    /**
     * @var
     */
    public $canonical;

    //geographicMeta

    /**
     * @var
     */
    public $region;

    /**
     * @var
     */
    public $placename;

    /**
     * @var
     */
    public $position;

    /**
     * @var
     */
    public $latitude;

    /**
     * @var
     */
    public $longitude;

    //openGraphMeta

    /**
     * @var
     */
    public $ogType;

    /**
     * @var
     */
    public $ogSiteName;

    /**
     * @var
     */
    public $ogAuthor;

    /**
     * @var
     */
    public $ogPublisher;

    /**
     * @var
     */
    public $ogUrl;

    /**
     * @var
     */
    public $ogTitle;

    /**
     * @var
     */
    public $ogDescription;

    /**
     * @var
     */
    public $ogImage;

    /**
     * @var
     */
    public $ogImageSecure;

    /**
     * @var
     */
    public $ogImageWidth;

    /**
     * @var
     */
    public $ogImageHeight;

    /**
     * @var
     */
    public $ogImageType;

    /**
     * @var
     */
    public $ogAudio;

    /**
     * @var
     */
    public $ogVideo;

    /**
     * @var
     */
    public $ogLocale;

    /**
     * @var
     */
    public $ogDateUpdated;

    /**
     * @var
     */
    public $ogDateCreated;

    /**
     * @var
     */
    public $ogExpiryDate;

    //twitterCardsMeta

    /**
     * @var
     */
    public $twitterCard;

    /**
     * @var
     */
    public $twitterSite;

    /**
     * @var
     */
    public $twitterCreator;

    /**
     * @var
     */
    public $twitterUrl;

    /**
     * @var
     */
    public $twitterTitle;

    /**
     * @var
     */
    public $twitterDescription;

    /**
     * @var
     */
    public $twitterImage;

    /**
     * @var
     */
    public $twitterPlayer;

    /**
     * @var
     */
    public $twitterPlayerStream;

    /**
     * @var
     */
    public $twitterPlayerStreamContentType;

    /**
     * @var
     */
    public $twitterPlayerWidth;

    /**
     * @var
     */
    public $twitterPlayerHeight;

    /**
     * @var
     */
    public $dateCreated;

    /**
     * @var
     */
    public $dateUpdated;

    /**
     * @var
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
    public function setMeta($type = MetadataLevels::GlobalMetadata, $overrideInfo = [])
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

        // moved to getPrioritizedMetadataModel just one time called.
        //SproutSeoOptimizeHelper::prepareAssetUrls($this);

        return $this;
    }

    /**
     * @return mixed
     * @throws \yii\base\Exception
     */
    protected function prepareGlobalMetadata($overrideInfo)
    {
        $globals = $overrideInfo['globals'] ?? SproutSeo::$app->optimize->globals;

        return $globals->meta;
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
            $element = $overrideInfo['contextElement'];
            $site = $element->getSite();

            $elementMetadata = $overrideInfo['metadataField'];
            $elementMetadata->ogLocale = $site->language;

            // Default to the current URL, if no overrides exist
            $elementMetadata->canonical = SproutSeoOptimizeHelper::prepareCanonical($elementMetadata);
            $elementMetadata->ogUrl = SproutSeoOptimizeHelper::prepareCanonical($elementMetadata);
            $elementMetadata->twitterUrl = SproutSeoOptimizeHelper::prepareCanonical($elementMetadata);

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
                    $value = SproutSeoOptimizeHelper::prepareGeoPosition($this);
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
        $tagData = SproutSeoOptimizeHelper::getGooglePlusPage();

        return $tagData;
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
     * @return string
     */
    public function getPreviewUrl()
    {
        $uri = $this->uri ?? '';

        return UrlHelper::url($uri);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'handle'], 'required'],
            ['uri', 'sectionUri', 'on' => 'customSection'],
            [['uri'], 'required', 'on' => 'customSection', 'message' => 'Uri cannot be blank.'],
        ];
    }

    /**
     *
     */
    public function getSchema()
    {
        //$schemaTypeId = $this->schemaTypeId;
        //
        //// Check for parent Section and Global values if the entry ones don't exist
        //
        //if ($schemaTypeId)
        //{
        //	$schema               = SproutSeo::$app->optimize->getSchemaByUniqueKey($schemaTypeId);
        //	$schema->attributes   = $this->getAttributes();
        //	$schema->addContext   = true;
        //	$schema->isMainEntity = false;
        //
        //	$element = Craft::$app->elements->getElementById($this->elementId);
        //
        //	SproutSeo::$app->optimize->urlEnabledSection = '';
        //	SproutSeo::$app->optimize->prioritizedMetadataModel = '';
        //	SproutSeo::$app->optimize->codeMetadata = null;
        //
        //	$schema->globals                  = SproutSeo::$app->optimize->globals;
        //	$schema->element                  = ;
        //	//$schema->prioritizedMetadataModel = $this->prioritizedMetadataModel;
        //}
        //
        //return $schema->getSchema();
    }


    /**
     * Check is the url saved on custom sections are URI's
     * This is the 'sectionUri' validator as declared in rules().
     *
     * @param $attribute
     * @param $params
     */
    public function sectionUri($attribute, $params)
    {
        if (UrlHelper::isAbsoluteUrl($this->$attribute)) {
            $this->addError($attribute, Craft::t('sprout-seo', 'Invalid URI'));
        }
    }

    /**
     * Updates "uri" to starts without a "/"
     */
    public function beforeValidate()
    {
        $this->uri = SproutSeo::$app->sitemap->removeSlash($this->uri);

        return true;
    }
}
