<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\fields;

use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbasefields\web\assets\selectother\SelectOtherFieldAsset;
use barrelstrength\sproutseo\models\Metadata;
use barrelstrength\sproutseo\SproutSeo;
use barrelstrength\sproutseo\web\assets\seo\SproutSeoAsset;
use barrelstrength\sproutseo\web\assets\tageditor\TagEditorAsset;
use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\db\mysql\Schema;
use craft\elements\Asset;
use craft\fields\Assets;
use craft\helpers\Json;

/**
 *
 * @property array       $elementValidationRules
 * @property string      $contentColumnType
 * @property null|string $settingsHtml
 */
class ElementMetadata extends Field
{
    /**
     * The active metadata
     *
     * @var Metadata
     */
    public $metadata;

    /**
     * An array of our metadata values to use for processing, validation, and handing
     * off to the db. We store these separately from the supported $value parameter because
     * the $value parameter helps managed handing back values after failed validation scenarios
     *
     * @var array()
     */
    public $values;

    public $optimizedTitleField;

    public $optimizedDescriptionField;

    public $optimizedImageField;

    public $optimizedKeywordsField;

    public $showMainEntity;

    public $showSearchMeta = false;

    public $showOpenGraph = false;

    public $showTwitter = false;

    public $showGeo = false;

    public $showRobots = false;

    public $editCanonical = false;

    public $schemaOverrideTypeId;

    public $schemaTypeId;

    public $enableMetaDetailsFields = false;

    /**
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('sprout-seo', 'Metadata (Sprout SEO)');
    }

    /**
     * @return string
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_TEXT;
    }

    /**
     * @inheritdoc
     */
    public function isValueEmpty($value, ElementInterface $element): bool
    {
        if (!$value instanceof Metadata) {
            return true;
        }

        $attributes = array_filter($value->getAttributes());

        return count($attributes) === 0;
    }

    /**
     * @param mixed                 $value
     * @param ElementInterface|null $element
     *
     * @return Metadata|mixed
     * @throws Exception
     * @throws InvalidConfigException
     * @throws SiteNotFoundException
     * @throws Throwable
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        $metadata = null;
        $metadataArray = null;

        // On page load and the resave element task the $value comes from the content table as json
        if (is_string($value)) {
            $metadataArray = Json::decode($value);
        }

        // when is resaving on all sites comes into array
        if (is_array($value)) {
            $metadataArray = $value;
        }

        // When is a post request the metadata values comes into the metadata key
        if (isset($value['metadata'])) {
            $metadataArray = $value['metadata'];
        }

        if (isset($metadataArray['sproutSeoSettings'])) {
            // removes json value from livepreview
            unset($metadataArray['sproutSeoSettings']);
        }

        if (isset($metadataArray)) {
            $metadata = new Metadata($metadataArray, $this, $element);

            $this->values = $metadata;
            return $metadata;
        }

        $this->values = $value;

        return $value;
    }

    /**
     * @param mixed                 $value
     * @param ElementInterface|null $element
     *
     * @return array|mixed|string|null
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        if ($value instanceof Metadata) {
            return Json::encode($value->getAttributes());
        }

        return $value;
    }

    /**
     * @return string|null
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function getSettingsHtml()
    {
        $schemas = SproutSeo::$app->schema->getSchemaOptions();
        $schemaSubtypes = SproutSeo::$app->schema->getSchemaSubtypes($schemas);

        Craft::$app->getView()->registerAssetBundle(SproutSeoAsset::class);
        Craft::$app->getView()->registerAssetBundle(SelectOtherFieldAsset::class);

        $isPro = SproutBase::$app->settings->isEdition('sprout-seo', SproutSeo::EDITION_PRO);

        return Craft::$app->view->renderTemplate('sprout-seo/_components/fields/elementmetadata/settings', [
            'fieldId' => $this->id,
            'settings' => $this->getAttributes(),
            'schemas' => $schemas,
            'field' => $this,
            'schemaSubtypes' => $schemaSubtypes,
            'isPro' => $isPro
        ]);
    }

    /**
     * @param mixed                 $value
     * @param ElementInterface|null $element
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $name = $this->handle;
        $inputId = Craft::$app->view->formatInputId($name);
        $namespaceInputName = Craft::$app->view->namespaceInputName($inputId);
        $namespaceInputId = Craft::$app->view->namespaceInputId($inputId);

        $ogImageElements = [];
        $metaImageElements = [];
        $twitterImageElements = [];

        // Set up our asset fields
        if ($value->optimizedImage) {
            // If validation fails, we need to make sure our asset is just an ID
            $value->optimizedImage = OptimizeHelper::getImageId($value->optimizedImage);
            $asset = Craft::$app->elements->getElementById($value->optimizedImage);
            $metaImageElements = [$asset];
        }

        if ($value->ogImage) {
            $value->ogImage = OptimizeHelper::getImageId($value->ogImage);
            $asset = Craft::$app->elements->getElementById($value->ogImage);
            $ogImageElements = [$asset];
        }

        if ($value->twitterImage) {
            $value->twitterImage = OptimizeHelper::getImageId($value->twitterImage);
            $asset = Craft::$app->elements->getElementById($value->twitterImage);
            $twitterImageElements = [$asset];
        }

        $value['robots'] = OptimizeHelper::prepareRobotsMetadataForSettings($value->robots);

        // Cleanup the namespace around the $name handle
        $name = str_replace('fields[', '', $name);
        $name = rtrim($name, ']');

        $fieldId = 'fields-'.$name.'-field';

        $name = "sproutseo[metadata][$name]";

        $settings = $this->getAttributes();

        Craft::$app->getView()->registerAssetBundle(SproutSeoAsset::class);
        Craft::$app->getView()->registerAssetBundle(TagEditorAsset::class);

        return Craft::$app->view->renderTemplate('sprout-seo/_components/fields/elementmetadata/input', [
            'field' => $this,
            'name' => $name,
            'namespaceInputName' => $namespaceInputName,
            'namespaceInputId' => $namespaceInputId,
            'values' => $value,
            'ogImageElements' => $ogImageElements,
            'twitterImageElements' => $twitterImageElements,
            'metaImageElements' => $metaImageElements,
            'assetElementClassName' => Asset::class,
            'fieldId' => $fieldId,
            'settings' => $settings
        ]);
    }

    /**
     * @return array
     */
    public function defineRules(): array
    {
        $isPro = SproutBase::$app->settings->isEdition('sprout-seo', SproutSeo::EDITION_PRO);
        $metadataFieldCount = (int)SproutSeo::$app->settings->getMetadataFieldCount();

        $theFirstMetadataField = !$this->id && $metadataFieldCount === 0;
        $theOneMetadataField = $this->id && $metadataFieldCount === 1;

        if (!$isPro && !($theFirstMetadataField || $theOneMetadataField)) {
            $this->addError('optimizedTitleField', Craft::t('sprout-seo', 'Upgrade to Sprout SEO PRO to manage multiple Metadata fields.'));
        }

        return parent::defineRules();
    }

    /**
     * @inheritdoc
     */
    public function getElementValidationRules(): array
    {
        $rules[] = 'validateElementMetadata';

        return $rules;
    }

    /**
     * Validates our fields submitted value beyond the checks
     * that were assumed based on the content attribute.
     *
     * @param Element $element
     *
     * @return void
     */
    public function validateElementMetadata(Element $element)
    {
        $isRequired = $this->required;

        if ($isRequired) {
            $optimizedTitle = $this->optimizedTitleField;
            $optimizedDescription = $this->optimizedDescriptionField;

            if ($optimizedTitle === 'manually' &&
                $optimizedDescription === 'manually'
            ) {
                if ($optimizedTitle === 'manually' && empty($this->values['optimizedTitle'])) {
                    $element->addError(
                        $this->handle,
                        Craft::t('sprout-seo', 'Meta Title field cannot be blank.')
                    );
                }

                if ($optimizedDescription === 'manually' && empty($this->values['optimizedDescription'])) {
                    $element->addError(
                        $this->handle,
                        Craft::t('sprout-seo', 'Meta Description field cannot be blank.')
                    );
                }
            }
        }
    }

    /**
     * @param bool $isNew
     *
     * @throws SiteNotFoundException
     */
    public function afterSave(bool $isNew)
    {
        SproutSeo::$app->elementMetadata->resaveElementsIfUsingElementMetadataField($this->id);

        parent::afterSave($isNew);
    }

    /**
     * @param $metadataModel Metadata
     *
     * @return mixed
     */
    protected function prepareExistingValuesForPage($metadataModel)
    {
        foreach ($metadataModel->getAttributes() as $key => $value) {
            if (($key === 'ogImage' || $key === 'twitterImage') && !empty($metadataModel->{$key})) {
                $metadataModel->{$key} = $metadataModel->{$key}[0];
            }

            if ($key === 'robots') {
                $metadataModel->{$key} = OptimizeHelper::prepareRobotsMetadataValue($metadataModel->{$key});
            }
        }

        return $metadataModel;
    }

    /**
     * @param $type
     * @param $value
     * @param $attributes
     *
     * @return mixed
     */
    private function setMetaDetailsValues($type, $value, $attributes)
    {
        $ogKey = 'og'.ucfirst($type);
        $twitterKey = 'twitter'.ucfirst($type);
        $ogValue = $attributes[$ogKey] ?? null;
        $twitterValue = $attributes[$twitterKey] ?? null;
        $searchValue = $attributes[$type] ?? null;

        // Default values
        $attributes[$type] = $value;
        $attributes[$ogKey] = $value;
        $attributes[$twitterKey] = $value;

        if (isset($attributes['enableMetaDetailsSearch']) && $attributes['enableMetaDetailsSearch'] && $searchValue) {
            $attributes[$type] = $searchValue;
        }

        if (isset($attributes['enableMetaDetailsOpenGraph']) && $attributes['enableMetaDetailsOpenGraph'] && $ogValue) {
            $attributes[$ogKey] = $ogValue;
        }

        if (isset($attributes['enableMetaDetailsTwitterCard']) && $attributes['enableMetaDetailsTwitterCard'] && $twitterValue) {
            $attributes[$twitterKey] = $twitterValue;
        }

        return $attributes;
    }

}