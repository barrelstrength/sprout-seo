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
use craft\errors\SiteNotFoundException;
use craft\helpers\Json;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 *
 * @property array       $elementValidationRules
 * @property string      $contentColumnType
 * @property null|string $settingsHtml
 */
class ElementMetadata extends Field
{
    public $optimizedTitleField;

    public $optimizedDescriptionField;

    public $optimizedImageField;

    public $optimizedKeywordsField;

    public $schemaTypeId;

    public $schemaOverrideTypeId;

    public $editCanonical = false;

    public $enableMetaDetailsFields = false;

    public $showSearchMeta = false;

    public $showOpenGraph = false;

    public $showTwitter = false;

    public $showGeo = false;

    public $showRobots = false;

    public function __construct($config = [])
    {
        // Make sure we don't try to assign removed properties
        // @todo - deprecate variables in 5.x
        // Already removed in migration: m200224_000001_update_element_metadata_field_settings
        unset($config['metadata'], $config['values'], $config['showMainEntity']);

        parent::__construct($config);
    }

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
     * @throws SiteNotFoundException
     * @throws Throwable
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        $metadata = null;
        $metadataArray = null;

        if ($value === null) {
            return $value;
        }

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

        $this->populateOptimizeServiceValues($element);

        return new Metadata($metadataArray ?? []);
    }

    /**
     * We use afterElementSave instead of serializeValue because serializeValue doesn't get called if
     * the field is not dirty and since the Element Metadata field watches other fields to determine
     * optimized values, it may not be dirty even if calculated values have changed.
     *
     * @param ElementInterface $element
     * @param bool             $isNew
     *
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     * @throws \yii\db\Exception
     */
    public function afterElementSave(ElementInterface $element, bool $isNew)
    {
        /** @var Metadata $value */
        /** @var Element $element */
        $value = $element->getFieldValue($this->handle);

        if ($value !== null) {
            $metadataJson = null;

            if ($value instanceof Metadata) {
                $metadataJson = Json::encode($value->getRawData());
            }

            $contentTable = Craft::$app->getContent()->contentTable;
            $fieldColumnPrefix = Craft::$app->getContent()->fieldColumnPrefix;
            $fieldName = $fieldColumnPrefix.$this->handle;

            Craft::$app->db->createCommand()->update($contentTable, [
                $fieldName => $metadataJson
            ], [
                'and',
                ['elementId' => $element->id],
                ['siteId' => $element->siteId],
            ], [], false)->execute();
        }

        parent::afterElementSave($element, $isNew);
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
            'field' => $this,
            'schemas' => $schemas,
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
     * @throws InvalidConfigException
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $name = $this->handle;
        $inputId = Craft::$app->view->formatInputId($name);
        $namespaceInputName = Craft::$app->view->namespaceInputName($inputId);
        $namespaceInputId = Craft::$app->view->namespaceInputId($inputId);

        // Cleanup the namespace around the $name handle
        $name = str_replace('fields[', '', $name);
        $name = rtrim($name, ']');

        $fieldId = 'fields-'.$name.'-field';

        $name = "sproutseo[metadata][$name]";

        $settings = $this->getAttributes();

        Craft::$app->getView()->registerAssetBundle(SproutSeoAsset::class);
        Craft::$app->getView()->registerAssetBundle(TagEditorAsset::class);

        // Make sure we have a metadata object for new entries
        if ($value === null) {
            $value = new Metadata();
            $this->populateOptimizeServiceValues($element);
        }

        return Craft::$app->view->renderTemplate('sprout-seo/_components/fields/elementmetadata/input', [
            'field' => $this,
            'name' => $name,
            'namespaceInputName' => $namespaceInputName,
            'namespaceInputId' => $namespaceInputId,
            'metaTypes' => $value->getMetaTypes(),
            'values' => $value->getRawData(),
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
        $value = $element->getFieldValue($this->handle);
        $isRequired = $this->required;

        if ($isRequired) {
            $optimizedTitle = $this->optimizedTitleField;
            $optimizedDescription = $this->optimizedDescriptionField;

            if ($optimizedTitle === 'manually' &&
                $optimizedDescription === 'manually'
            ) {
                if ($optimizedTitle === 'manually' && empty($value['optimizedTitle'])) {
                    $element->addError(
                        $this->handle,
                        Craft::t('sprout-seo', 'Meta Title field cannot be blank.')
                    );
                }

                if ($optimizedDescription === 'manually' && empty($value['optimizedDescription'])) {
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
     * @param ElementInterface $element
     *
     * @throws Exception
     * @throws SiteNotFoundException
     */
    protected function populateOptimizeServiceValues(ElementInterface $element = null)
    {
        /** @var Element $element */
        $site = isset($element)
            ? Craft::$app->sites->getSiteById($element->siteId)
            : Craft::$app->sites->getPrimarySite();

        $globals = SproutSeo::$app->globalMetadata->getGlobalMetadata($site);

        SproutSeo::$app->optimize->globals = $globals;
        SproutSeo::$app->optimize->element = $element;
        SproutSeo::$app->optimize->elementMetadataField = $this;
    }
}