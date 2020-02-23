<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\models;


use craft\base\Model;

/**
 * Class Metadata
 *
 * @property null   $googlePlusMetaTagData
 * @property array  $searchMetaTagData
 * @property array  $robotsMetaTagData
 * @property array  $twitterCardMetaTagData
 * @property array  $geographicMetaTagData
 * @property array  $metaTagData
 * @property array  $openGraphMetaTagData
 * @property string uri
 */
class Metadata extends Model
{
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
     * @var int
     */
    public $elementId;

    /**
     * @var string
     */
    public $twitterTransform;

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

    //MetaTags

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

    /**
     * @var
     */
    public $robots;

    /**
     * @var string
     */
    public $canonical;

    //searchMeta

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

    //robotsMeta

    /**
     * @var string
     */
    public $latitude;

    /**
     * @var string
     */
    public $longitude;

    //geographicMeta

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

    //openGraphMeta

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

    //twitterCardsMeta

    /**
     * @var string
     */
    public $twitterDescription;

    /**
     * @var string
     */
    public $twitterImage;

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
     * @inheritdoc
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
            'twitterImage' => $this->twitterImage
        ];
    }

    /**
     * @return array
     * @throws \yii\base\Exception
     */
    public function getMetaTagData(): array
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
     * @return null
     * @throws \yii\base\Exception
     */
    public function getGooglePlusMetaTagData()
    {
        return OptimizeHelper::getGooglePlusPage();
    }

    /**
     * Updates "uri" to starts without a "/"
     *
     * @return bool
     */
    public function beforeValidate(): bool
    {
        $this->uri = SproutBaseRedirects::$app->redirects->removeSlash($this->uri);

        return true;
    }

    /**
     * @return array
     */
    protected function getSearchMetaTagData(): array
    {
        $tagData = [];

        foreach ($this->searchMeta as $key => $value) {
            if ($this->{$key}) {
                $value = $this->{$key};
                $tagData[$key] = $value;
            }
        }

        return $tagData;
    }

    /**
     * @return array
     */
    protected function getRobotsMetaTagData(): array
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
    protected function getGeographicMetaTagData(): array
    {
        $tagData = [];

        foreach ($this->geographicMeta as $key => $value) {
            if ($key === 'latitude' or $key === 'longitude') {
                break;
            }

            $value = $this->{$key};

            if ($key === 'position') {
                $value = OptimizeHelper::prepareGeoPosition($this);
            }

            if ($value) {
                $tagData[$this->getMetaTagName($key)] = $value;
            }
        }

        return $tagData;
    }

    /**
     * @return array
     */
    protected function getOpenGraphMetaTagData(): array
    {
        $tagData = [];

        foreach ($this->openGraphMeta as $key => $value) {
            if ($this->{$key}) {
                $value = $this->{$key};
                $tagData[$this->getMetaTagName($key)] = $value;
            }
        }

        return $tagData;
    }

    /**
     * @return array
     */
    protected function getTwitterCardMetaTagData(): array
    {
        $tagData = [];

        foreach ($this->twitterCardsMeta as $key => $value) {
            if ($this->{$key}) {
                $value = $this->{$key};
                $tagData[$this->getMetaTagName($key)] = $value;
            }
        }

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
            'twitterImage' => 'twitter:image'
        ];

        return $tagNames[$handle];
    }
}
