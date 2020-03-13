<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\meta;

use barrelstrength\sproutseo\base\MetaImageTrait;
use barrelstrength\sproutseo\base\MetaType;
use barrelstrength\sproutseo\SproutSeo;
use Craft;
use craft\base\Field;
use craft\errors\SiteNotFoundException;
use DateTime;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;

/**
 * Implements all attributes used in search metadata
 */
class OpenGraphMetaType extends MetaType
{
    use MetaImageTrait;

    /**
     * @var string|null
     */
    protected $ogType;

    /**
     * @var string|null
     */
    protected $ogSiteName;

    /**
     * @var string|null
     */
    protected $ogAuthor;

    /**
     * @var string|null
     */
    protected $ogPublisher;

    /**
     * @var string|null
     */
    protected $ogUrl;

    /**
     * @var string|null
     */
    protected $ogTitle;

    /**
     * @var string|null
     */
    protected $ogDescription;

    /**
     * @var string|null
     */
    protected $ogImage;

    /**
     * @var string|null
     */
    protected $ogImageSecure;

    /**
     * @var int
     */
    protected $ogImageWidth;

    /**
     * @var int|null
     */
    protected $ogImageHeight;

    /**
     * @var string|null
     */
    protected $ogImageType;

    /**
     * @var string|null
     */
    protected $ogTransform;

    /**
     * @var string|null
     */
    protected $ogLocale;

    /**
     * @var DateTime|null
     */
    protected $ogDateUpdated;

    /**
     * @var DateTime|null
     */
    protected $ogDateCreated;

    /**
     * @var DateTime|null
     */
    protected $ogExpiryDate;

    public static function displayName(): string
    {
        return Craft::t('sprout-seo', 'Open Graph');
    }

    public function attributes(): array
    {
        $attributes = parent::attributes();
        $attributes[] = 'ogType';
        $attributes[] = 'ogSiteName';
        $attributes[] = 'ogPublisher';
        $attributes[] = 'ogAuthor';
        $attributes[] = 'ogUrl';
        $attributes[] = 'ogTitle';
        $attributes[] = 'ogDescription';
        $attributes[] = 'ogImage';
        $attributes[] = 'ogImageSecure';
        $attributes[] = 'ogImageWidth';
        $attributes[] = 'ogImageHeight';
        $attributes[] = 'ogImageType';
        $attributes[] = 'ogTransform';
        $attributes[] = 'ogLocale';
        $attributes[] = 'ogDateCreated';
        $attributes[] = 'ogDateUpdated';
        $attributes[] = 'ogExpiryDate';

        return $attributes;
    }

    /**
     * @return array
     */
    public function getAttributesMapping(): array
    {
        return [
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
            'ogExpiryDate' => 'article:expiration_time'
        ];
    }

    public function getHandle(): string
    {
        return 'openGraph';
    }

    public function getIconPath(): string
    {
        return '@sproutbaseicons/facebook-f.svg';
    }

    /**
     * @param Field $field
     *
     * @return string
     * @throws Exception
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getSettingsHtml(Field $field): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-seo/_components/fields/elementmetadata/blocks/open-graph', [
            'meta' => $this,
            'field' => $field
        ]);
    }

    public function showMetaDetailsTab(): bool
    {
        return SproutSeo::$app->optimize->elementMetadataField->showOpenGraph;
    }

    /**
     * @return string|null
     */
    public function getOgType()
    {
        if ($this->ogType || $this->metadata->getRawDataOnly()) {
            return $this->ogType;
        }

        return SproutSeo::$app->optimize->globals->settings['defaultOgType'] ?? 'website';
    }

    /**
     * @param $value
     */
    public function setOgType($value)
    {
        $this->ogType = $value;
    }

    /**
     * @return string|null
     */
    public function getOgSiteName()
    {
        if ($this->ogSiteName || $this->metadata->getRawDataOnly()) {
            return $this->ogSiteName;
        }

        return SproutSeo::$app->optimize->globals->identity['name'] ?? Craft::$app->getSystemName();
    }

    /**
     * @param $value
     */
    public function setOgSiteName($value)
    {
        $this->ogSiteName = $value;
    }

    /**
     * @return string|null
     */
    public function getOgAuthor()
    {
        if ($this->getOgType() !== 'article' || $this->metadata->getRawDataOnly()) {
            return null;
        }

        return $this->ogAuthor;
    }

    /**
     * @param $value
     */
    public function setOgAuthor($value)
    {
        $this->ogAuthor = $value;
    }

    /**
     * @return string|null
     */
    public function getOgPublisher()
    {
        if ($this->getOgType() !== 'article' || $this->metadata->getRawDataOnly()) {
            return null;
        }

        if ($this->ogPublisher) {
            return $this->ogPublisher;
        }

        $socialSettings = SproutSeo::$app->optimize->globals->social;
        $facebookPage = $this->getFacebookPage($socialSettings);

        return $facebookPage ?? null;
    }

    /**
     * @param $value
     */
    public function setOgPublisher($value)
    {
        $this->ogPublisher = $value;
    }

    /**
     * @return string|null
     * @throws Exception
     */
    public function getOgUrl()
    {
        if ($this->ogUrl || $this->metadata->getRawDataOnly()) {
            return $this->ogUrl;
        }

        return $this->metadata->getCanonical();
    }

    /**
     * @param $value
     */
    public function setOgUrl($value)
    {
        $this->ogUrl = $value;
    }

    /**
     * @return string|null
     * @throws Exception
     * @throws Throwable
     */
    public function getOgTitle()
    {
        if ($this->ogTitle || $this->getRawDataOnly()) {
            return trim($this->ogTitle);
        }

        if ($optimizedTitle = $this->metadata->getOptimizedTitle()) {
            return trim($optimizedTitle) ?: null;
        }

        return trim(SproutSeo::$app->optimize->globals->identity['name']);
    }

    /**
     * @param $value
     */
    public function setOgTitle($value)
    {
        $this->ogTitle = $value;
    }

    /**
     * @return string|null
     * @throws Exception
     * @throws Throwable
     */
    public function getOgDescription()
    {
        $descriptionLength = SproutSeo::$app->settings->getDescriptionLength();

        if ($this->ogDescription || $this->metadata->getRawDataOnly()) {
            return mb_substr($this->ogDescription, 0, $descriptionLength) ?: null;
        }

        if ($optimizedDescription = $this->metadata->getOptimizedDescription()) {
            return mb_substr($optimizedDescription, 0, $descriptionLength) ?: null;
        }

        $globalDescription = SproutSeo::$app->optimize->globals->identity['description'] ?? null;

        return mb_substr($globalDescription, 0, $descriptionLength) ?: null;
    }

    /**
     * @param $value
     */
    public function setOgDescription($value)
    {
        $this->ogDescription = $value;
    }

    /**
     * @return string|null
     */
    public function getOgImageSecure()
    {
        if ($this->metadata->getRawDataOnly()) {
            return null;
        }

        return $this->ogImageSecure;
    }

    /**
     * @param $value
     */
    public function setOgImageSecure($value)
    {
        $this->ogImageSecure = $value;
    }

    /**
     * @return mixed|string|null
     * @throws Exception
     * @throws Throwable
     */
    public function getOgImage()
    {
        if ($this->ogImage || $this->metadata->getRawDataOnly()) {
            return $this->ogImage;
        }

        if ($optimizedImage = $this->metadata->getOptimizedImage()) {
            return $this->normalizeImageValue($optimizedImage);
        }

        return SproutSeo::$app->optimize->globals->identity['image'] ?? null;
    }

    /**
     * @param $value
     *
     * @throws Throwable
     */
    public function setOgImage($value)
    {
        if (is_array($value)) {
            $this->ogImage = $value[0] ?? null;
        } else {
            $this->ogImage = $value;
        }
    }

    /**
     * @return int|null
     */
    public function getOgImageWidth()
    {
        return $this->ogImageWidth;
    }

    /**
     * @param $value
     */
    public function setOgImageWidth($value)
    {
        $this->ogImageWidth = $value;
    }

    /**
     * @return int|null
     */
    public function getOgImageHeight()
    {
        return $this->ogImageHeight;
    }

    /**
     * @param $value
     */
    public function setOgImageHeight($value)
    {
        $this->ogImageHeight = $value;
    }

    /**
     * @return string|null
     */
    public function getOgImageType()
    {
        if ($this->ogImageType || $this->metadata->getRawDataOnly()) {
            return $this->ogImageType;
        }

        return null;
    }

    /**
     * @param $value
     */
    public function setOgImageType($value)
    {
        $this->ogImageType = $value;
    }

    /**
     * @return string|null
     */
    public function getOgTransform()
    {
        if ($this->ogTransform || $this->metadata->getRawDataOnly()) {
            return $this->ogTransform;
        }

        return SproutSeo::$app->optimize->globals->settings['ogTransform'] ?? null;
    }

    /**
     * @param $value
     */
    public function setOgTransform($value)
    {
        $this->ogTransform = $value;
    }

    /**
     * @return string|null
     * @throws SiteNotFoundException
     */
    public function getOgLocale()
    {
        if ($this->ogLocale || $this->metadata->getRawDataOnly()) {
            return $this->ogLocale;
        }

        $site = Craft::$app->sites->getCurrentSite();

        return $site->language ?? null;
    }

    /**
     * @param $value
     */
    public function setOgLocale($value)
    {
        $this->ogLocale = $value;
    }

    /**
     * @return string|null
     */
    public function getOgDateUpdated()
    {
        if ($this->metadata->getRawDataOnly()) {
            return null;
        }

        $element = SproutSeo::$app->optimize->element;

        if ($element) {
            $dateUpdated = $element->dateUpdated ?? null;

            if ($dateUpdated) {
                return $dateUpdated->format(DateTime::ATOM);
            }
        }

        return $this->ogDateUpdated;
    }

    /**
     * @param $value
     */
    public function setOgDateUpdated($value)
    {
        $this->ogDateUpdated = $value;
    }

    /**
     * @return string|null
     */
    public function getOgDateCreated()
    {
        if ($this->metadata->getRawDataOnly()) {
            return null;
        }

        $element = SproutSeo::$app->optimize->element;

        if ($element) {
            $postDate = $element->postDate ?? null;

            if ($postDate) {
                return $postDate->format(DateTime::ATOM);
            }

            $dateUpdated = $element->dateUpdated ?? null;

            if ($dateUpdated) {
                return $dateUpdated->format(DateTime::ATOM);
            }
        }

        return $this->ogDateCreated;
    }

    /**
     * @param $value
     */
    public function setOgDateCreated($value)
    {
        $this->ogDateCreated = $value;
    }

    /**
     * @return string|null
     */
    public function getOgExpiryDate()
    {
        if ($this->metadata->getRawDataOnly()) {
            return null;
        }

        $element = SproutSeo::$app->optimize->element;

        if ($element) {
            $expiryDate = $element->expiryDate ?? null;

            if ($expiryDate) {
                return $expiryDate->format(DateTime::ATOM);
            }
        }

        return $this->ogExpiryDate;
    }

    /**
     * @param $value
     */
    public function setOgExpiryDate($value)
    {
        $this->ogExpiryDate = $value;
    }

    /**
     * @return array
     * @throws Exception
     * @throws SiteNotFoundException
     */
    public function getMetaTagData(): array
    {
        $tagData = parent::getMetaTagData();

        if (isset($tagData['og:image'])) {
            list(
                $tagData['og:image'],
                $tagData['og:image:width'],
                $tagData['og:image:height'],
                $tagData['og:image:type']
                ) = $this->prepareAssetMetaData($tagData['og:image'], $this->getOgTransform(), false);

            $tagData['og:image:secure_url'] = $tagData['og:image'];
        }

        return array_filter($tagData);
    }

    /**
     * Returns the first Facebook Page found in the Social Profile settings
     *
     * @param $socialProfiles
     *
     * @return null|string
     */
    public function getFacebookPage($socialProfiles = null)
    {
        if ($socialProfiles === null) {
            return null;
        }

        $facebookUrl = null;

        foreach ($socialProfiles as $profile) {
            $socialProfileNameFromPost = $profile[0] ?? null;
            $socialProfileNameFromSettings = $profile['profileName'] ?? null;

            // Support syntax for both POST data being saved and previous saved social settings
            if ($socialProfileNameFromPost === 'Facebook' || $socialProfileNameFromSettings === 'Facebook') {
                $facebookUrlFromPost = isset($socialProfileNameFromPost) ? $profile[1] : null;
                $facebookUrl = $socialProfileNameFromSettings !== null ? $profile['url'] : $facebookUrlFromPost;

                break;
            }
        }

        return $facebookUrl;
    }
}
