<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\models;

use barrelstrength\sproutseo\base\MetaImageTrait;
use barrelstrength\sproutseo\base\MetaType;
use barrelstrength\sproutseo\base\OptimizedTrait;
use barrelstrength\sproutseo\meta\GeoMetaType;
use barrelstrength\sproutseo\meta\OpenGraphMetaType;
use barrelstrength\sproutseo\meta\RobotsMetaType;
use barrelstrength\sproutseo\meta\SchemaMetaType;
use barrelstrength\sproutseo\meta\SearchMetaType;
use barrelstrength\sproutseo\meta\TwitterMetaType;
use barrelstrength\sproutseo\SproutSeo;
use Craft;
use craft\base\Model;
use PhpScience\TextRank\TextRankFacade;
use PhpScience\TextRank\Tool\StopWords\English;
use PhpScience\TextRank\Tool\StopWords\French;
use PhpScience\TextRank\Tool\StopWords\German;
use PhpScience\TextRank\Tool\StopWords\Italian;
use PhpScience\TextRank\Tool\StopWords\Norwegian;
use PhpScience\TextRank\Tool\StopWords\Spanish;
use RuntimeException;
use Throwable;
use yii\base\Exception;
use yii\base\InvalidConfigException;

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
    use OptimizedTrait;
    use MetaImageTrait;

    /**
     * @var MetaType[]
     */
    protected $metaTypes = [];

    /**
     * Metadata constructor.
     *
     * @param array $config
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
        // Populate the Optimized variables and unset them from the config
        $this->setOptimizedProperties($config);

        // Populate the MetaType models and unset any attributes that get assigned
        $this->setMetaTypes($config);

        parent::__construct($config);
//        $this->createComputedMetadata();
    }

    public function attributes(): array
    {
        $attributes = parent::attributes();
        $attributes[] = 'optimizedTitle';
        $attributes[] = 'optimizedDescription';
        $attributes[] = 'optimizedImage';
        $attributes[] = 'optimizedKeywords';
        $attributes[] = 'canonical';

        return $attributes;
    }

    /**
     * @return string|null
     */
    public function getOptimizedTitle()
    {
        return $this->optimizedTitle;
    }

    /**
     * @param string $value
     *
     * @throws Exception
     * @throws Throwable
     */
    public function setOptimizedTitle(string $value)
    {
        $element = SproutSeo::$app->optimize->element;
        $elementMetadataField = SproutSeo::$app->optimize->elementMetadataField;

        $optimizedTitleFieldSetting = $elementMetadataField->optimizedTitleField ?? 'manually';

        switch (true) {
            // Element Title
            case ($optimizedTitleFieldSetting === 'elementTitle'):

                $title = $element->title;

                break;

            // Manual Title
            case ($optimizedTitleFieldSetting === 'manually'):

                $title = $value;

                break;

            // Custom Field
            case (is_numeric($optimizedTitleFieldSetting)):

                $title = $this->getSelectedFieldForOptimizedMetadata($optimizedTitleFieldSetting);
                break;

            // Custom Value
            default:
                $title = Craft::$app->view->renderObjectTemplate($optimizedTitleFieldSetting, $element);

                break;
        }

        $this->optimizedTitle = $title;
    }

    /**
     * @return string|null
     */
    public function getOptimizedDescription()
    {
        $descriptionLength = SproutSeo::$app->settings->getDescriptionLength();

        return mb_substr($this->optimizedDescription, 0, $descriptionLength);
    }

    /**
     * @param string $value
     *
     * @throws Throwable
     * @throws Exception
     */
    public function setOptimizedDescription(string $value)
    {
        $element = SproutSeo::$app->optimize->element;
        $elementMetadataField = SproutSeo::$app->optimize->elementMetadataField;

        $optimizedDescriptionFieldSetting = $elementMetadataField->optimizedDescriptionField ?? 'manually';

        switch (true) {
            // Manual Description
            case ($optimizedDescriptionFieldSetting === 'manually'):

                $description = $value ?? null;

                break;

            // Custom Description
            case (is_numeric($optimizedDescriptionFieldSetting)):

                $description = $this->getSelectedFieldForOptimizedMetadata($optimizedDescriptionFieldSetting);

                break;

            // Custom Value
            default:

                $description = Craft::$app->view->renderObjectTemplate($optimizedDescriptionFieldSetting, $element);

                break;
        }

        // Just save the first 255 characters (we only output 160...)
        $description = mb_substr(trim($description), 0, 255);

        $this->optimizedDescription = $description;
    }

    /**
     * @return int|null
     */
    public function getOptimizedImage()
    {
        return $this->optimizedImage;
    }

    /**
     * @param $value
     *
     * @throws Exception
     * @throws Throwable
     */
    public function setOptimizedImage($value)
    {
        $this->optimizedImage = $this->normalizeImageValue($value);
    }

    /**
     * @return string|null
     */
    public function getOptimizedKeywords()
    {
        return $this->optimizedKeywords;
    }

    /**
     * @param null $value
     *
     * @throws InvalidConfigException
     */
    public function setOptimizedKeywords($value = null)
    {
        $keywords = $value;

        $element = SproutSeo::$app->optimize->element;
        $elementMetadataField = SproutSeo::$app->optimize->elementMetadataField;

        $optimizedKeywordsFieldSetting = $elementMetadataField->optimizedKeywordsField ?? 'manually';

        switch (true) {
            // Manual Keywords
            case ($optimizedKeywordsFieldSetting === 'manually'):

                $keywords = $value ?? null;

                break;

            // Auto-generate keywords from target field
            case (is_numeric($optimizedKeywordsFieldSetting)):

                $bigKeywords = $this->getSelectedFieldForOptimizedMetadata($optimizedKeywordsFieldSetting);
                $keywords = null;

                if ($bigKeywords) {

                    $textRankApi = new TextRankFacade();

                    $stopWordsMap = [
                        'en' => English::class,
                        'fr' => French::class,
                        'de' => German::class,
                        'it' => Italian::class,
                        'nn' => Norwegian::class,
                        'es' => Spanish::class
                    ];

                    $language = $element->getSite()->language;
                    $languagePrefixArray = explode('-', $language);

                    $stopWordsClass = $stopWordsMap['en'];

                    if (count($languagePrefixArray) > 0) {
                        $languagePrefix = $languagePrefixArray[0];

                        if (isset($stopWordsMap[$languagePrefix])) {
                            $stopWordsClass = $stopWordsMap[$languagePrefix];
                        }
                    }

                    $stopWords = new $stopWordsClass();

                    try {
                        $textRankApi->setStopWords($stopWords);

                        $rankedKeywords = $textRankApi->getOnlyKeyWords($bigKeywords);
                        $fiveKeywords = array_keys(array_slice($rankedKeywords, 0, 5));
                        $keywords = implode(',', $fiveKeywords);
                    } catch (RuntimeException $e) {
                        // Cannot detect the language of the text, maybe to short.
                        $keywords = null;
                    }
                }

                break;
        }

        $this->optimizedKeywords = $keywords;
    }

    /**
     * @param $value
     */
    public function setCanonical($value)
    {
        $this->canonical = $value;
    }

    /**
     * @param array $config
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function setOptimizedProperties(array &$config = [])
    {
        // Ensure we set all optimized values even if no value is received
        // when configuring the Metadata model. Configuration may happen on the field type
        foreach ($this->getAttributes() as $key => $value) {
            $setter = 'set'.ucfirst($key);
            $optimizedSettingValue = $config[$key] ?? null;
            if ($optimizedSettingValue) {
                $this->{$setter}($optimizedSettingValue);
            }
            unset($config[$key]);
        }
    }

    /**
     * @return MetaType[]
     */
    public function getMetaTypes(): array
    {
        return $this->metaTypes;
    }

    /**
     * @param array $config
     */
    protected function setMetaTypes(array &$config = [])
    {
        $metaTypes = [
            new SearchMetaType(),
            new OpenGraphMetaType(),
            new TwitterMetaType(),
            new GeoMetaType(),
            new RobotsMetaType(),
            new SchemaMetaType()
        ];

        foreach ($metaTypes as $metaType) {
            $this->populateMetaType($config, $metaType);
        }
    }

    /**
     * Returns metadata as a flat array of the base values stored on the model.
     * The raw data is stored in the database and used when submitting related forms.
     * This method does not return any calculated values.
     *
     * @return array
     */
    public function getRawData(): array
    {
        $metaForDb = [];

        foreach ($this->metaTypes as $metaType) {
            $staticAttributes = $metaType->getRawData();

            foreach ($staticAttributes as $key => $attribute) {
                $metaForDb[$attribute] = $metaType[$attribute];
            }
        }

        // Merge Optimized Values and Meta Type values
        return array_merge($this->getAttributes(), $metaForDb);
    }

    /**
     * Returns the calculated values for the metadata used in the front-end meta tags.
     *
     * @return array
     */
    public function getMetaTagData(): array
    {
        $metaTagData = [];

        foreach ($this->metaTypes as $metaType) {
            $metaTagData[$metaType->getHandle()] = $metaType->getMetaTagData();
        }

        return $metaTagData;
    }

    /**
     * @param array    $config
     * @param MetaType $metaType
     */
    protected function populateMetaType(array &$config, MetaType $metaType)
    {
        // Match the values being populated to a given Meta Type model
        $metaAttributes = array_intersect_key($config, $metaType->getAttributes());

        // Assign the Metadata Optimized variables to the Meta Type classes so they can be used as fallbacks
        $metaType->optimizedTitle = $this->optimizedTitle;
        $metaType->optimizedDescription = $this->optimizedDescription;
        $metaType->optimizedImage = $this->optimizedImage;
        $metaType->optimizedKeywords = $this->optimizedKeywords;
        $metaType->canonical = $this->canonical;

        foreach ($metaAttributes as $key => $value) {
            // Build the setter name dynamically: i.e. ogTitle => setOgTitle()
            $setter = 'set'.ucfirst($key);
            if ($value) {
                $metaType->{$setter}($value);
            }
            unset($config[$key]);
        }

        $this->metaTypes[$metaType->handle] = $metaType;
    }
}
