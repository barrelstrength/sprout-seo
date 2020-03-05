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
use barrelstrength\sproutseo\base\SchemaTrait;
use barrelstrength\sproutseo\meta\GeoMetaType;
use barrelstrength\sproutseo\meta\OpenGraphMetaType;
use barrelstrength\sproutseo\meta\RobotsMetaType;
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
 * @property array  $rawData
 * @property array  $optimizedProperties
 * @property string uri
 */
class Metadata extends Model
{
    use OptimizedTrait;
    use MetaImageTrait;
    use SchemaTrait;

    /**
     * @var MetaType[]
     */
    protected $metaTypes = [];

    protected $rawDataOnly = false;

    /**
     * Metadata constructor.
     *
     * @param array $config
     *
     * @throws Throwable
     */
    public function __construct($config = [])
    {
        // Unset any deprecated properties
        // @todo - deprecate variables in 5.x
        // Need to be removed by resaving all Elements with updated Metadata Model
        unset($config['enableMetaDetailsSearch'], $config['enableMetaDetailsOpenGraph'], $config['enableMetaDetailsTwitterCard'], $config['enableMetaDetailsGeo'], $config['enableMetaDetailsRobots'], $config['dateCreated'], $config['dateUpdated'], $config['uid'], $config['elementId']);

        // Remove any null or empty string values from the provided configuration
        $config = array_filter($config);

        // Populate the Optimized variables and unset them from the config
        $this->setOptimizedProperties($config);

        // Populate the MetaType models and unset any attributes that get assigned
        $this->setMetaTypes($config);

        // Schema properties will be derived from global and field settings
        $this->setSchemaProperties();

        parent::__construct($config);
    }

    public function getRawDataOnly(): bool
    {
        return $this->rawDataOnly;
    }

    public function setRawDataOnly(bool $value)
    {
        $this->rawDataOnly = $value;
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
     * @param array $config
     *
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
     * Determines the schema settings from the Global and Element Metadata field settings
     */
    public function setSchemaProperties()
    {
        $identity = SproutSeo::$app->optimize->globals['identity'] ?? null;
        $elementMetadataField = SproutSeo::$app->optimize->elementMetadataField ?? null;

        $globalSchemaTypeId = null;
        $globalSchemaOverrideTypeId = null;
        $elementMetadataFieldSchemaTypeId = null;
        $elementMetadataFieldSchemaOverrideTypeId = null;

        if (isset($identity['@type']) && $identity['@type']) {
            $globalSchemaTypeId = $identity['@type'];
        }

        if (isset($identity['organizationSubTypes']) && count($identity['organizationSubTypes'])) {
            $schemaSubTypes = array_filter($identity['organizationSubTypes']);
            // Get most specific override value
            $schemaOverrideTypeId = end($schemaSubTypes);
            $globalSchemaOverrideTypeId = $schemaOverrideTypeId;
        }

        if ($elementMetadataField) {
            if (!empty($elementMetadataField->schemaTypeId)) {
                $elementMetadataFieldSchemaTypeId = $elementMetadataField->schemaTypeId;
            }
            if (!empty($elementMetadataField->schemaOverrideTypeId)) {
                $elementMetadataFieldSchemaOverrideTypeId = $elementMetadataField->schemaOverrideTypeId;
            }
        }

        $schemaTypeId = $elementMetadataFieldSchemaTypeId ?? $globalSchemaTypeId ?? null;
        $schemaOverrideTypeId = $elementMetadataFieldSchemaOverrideTypeId ?? $globalSchemaOverrideTypeId ?? null;

        $this->setSchemaTypeId($schemaTypeId);
        $this->setSchemaOverrideTypeId($schemaOverrideTypeId);
    }

    /**
     * @param string|null $handle
     *
     * @return MetaType|MetaType[]
     */
    public function getMetaTypes(string $handle = null)
    {
        if ($handle) {
            return $this->metaTypes[$handle] ?? null;
        }

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
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function getRawData(): array
    {
        $metaForDb = [];

        $this->setRawDataOnly(true);
        $metaForDb['optimizedTitle'] = $this->getOptimizedTitle();
        $metaForDb['optimizedDescription'] = $this->getOptimizedDescription();
        $metaForDb['optimizedImage'] = $this->getOptimizedImage();
        $metaForDb['optimizedKeywords'] = $this->getOptimizedKeywords();
        $metaForDb['canonical'] = $this->getCanonical();

        foreach ($this->metaTypes as $metaType) {
            $metaType->setRawDataOnly(true);
            $staticAttributes = $metaType->getRawData();

            foreach ($staticAttributes as $key => $attribute) {
                $getter = 'get'.ucfirst($attribute);
                if (method_exists($metaType, $getter)) {
                    $value = $metaType->{$getter}();
                    $metaForDb[$attribute] = $value;
                }
            }
        }

        return $metaForDb;
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
            $metaTagByType = $metaType->getMetaTagData();

            // Remove blank or null values
            $metaTagData[$metaType->getHandle()] = array_filter($metaTagByType);
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
        $metaType->optimizedTitle = $this->getOptimizedTitle();
        $metaType->optimizedDescription = $this->getOptimizedDescription();
        $metaType->optimizedImage = $this->getOptimizedImage();
        $metaType->optimizedKeywords = $this->getOptimizedKeywords();
        $metaType->canonical = $this->getCanonical();

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
