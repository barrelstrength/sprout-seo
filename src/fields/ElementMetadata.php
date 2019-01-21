<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\fields;


use barrelstrength\sproutbase\app\fields\web\assets\selectother\SelectOtherFieldAsset;
use barrelstrength\sproutbase\app\seo\web\assets\schema\SchemaAsset;
use barrelstrength\sproutbase\app\seo\web\assets\opengraph\OpenGraphAsset;
use barrelstrength\sproutbase\app\seo\web\assets\tageditor\TagEditorAsset;
use barrelstrength\sproutbase\app\seo\web\assets\twittercard\TwitterCardAsset;
use barrelstrength\sproutseo\helpers\OptimizeHelper;
use barrelstrength\sproutseo\SproutSeo;
use barrelstrength\sproutseo\models\Metadata;
use barrelstrength\sproutbase\app\seo\web\assets\base\BaseAsset;
use craft\base\Element;
use craft\base\Field;
use Craft;
use PhpScience\TextRank\TextRankFacade;
use PhpScience\TextRank\Tool\StopWords\English;
use PhpScience\TextRank\Tool\StopWords\German;
use PhpScience\TextRank\Tool\StopWords\French;
use PhpScience\TextRank\Tool\StopWords\Italian;
use PhpScience\TextRank\Tool\StopWords\Norwegian;
use PhpScience\TextRank\Tool\StopWords\Spanish;
use yii\base\Exception;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use craft\elements\Asset;
use craft\fields\Assets;
use craft\db\mysql\Schema;

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
    public $showSearchMeta;
    public $showOpenGraph;
    public $showTwitter;
    public $showGeo;
    public $showRobots;
    public $editCanonical;
    public $schemaOverrideTypeId;
    public $schemaTypeId;
    public $enableMetaDetailsFields;

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
        return count($value) === 0;
    }

    /**
     * SerializeValue renamed from Craft2 - prepValue
     *
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return array|mixed|null|string
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        // $value will be an array if there was a validation error or we're loading a draft/version.
        // If we have a value, we are probably loading a Draft or Invalid Entry so let's override any
        // of those values. We need to undo a few things about how the Draft data gets stored so
        // that it gets reprocessed properly

        if (is_array($value)) {
            $value = $this->getMetadataFieldValues($value, $element);
            return json_encode($value);
        }

        // For the CP, return a Metadata
        return $value;
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     * @throws \Twig_Error_Loader
     * @throws \yii\base\InvalidConfigException
     */
//    public function getTableAttributeHtml($value, ElementInterface $element): string
//    {
//        Craft::$app->view->registerAssetBundle(BaseAsset::class);
//
//        $html = Craft::$app->view->renderTemplate('sprout-seo/_includes/metadata-status-icons', [
//            'sitemapSection' => $value
//        ]);
//
//        return $html;
//    }

    /**
     * @return null|string
     * @throws Exception
     * @throws \Twig_Error_Loader
     */
    public function getSettingsHtml()
    {
        $schemas = SproutSeo::$app->schema->getSchemaOptions();
        $schemaSubtypes = SproutSeo::$app->schema->getSchemaSubtypes($schemas);

        Craft::$app->getView()->registerAssetBundle(BaseAsset::class);
        Craft::$app->getView()->registerAssetBundle(SchemaAsset::class);
        Craft::$app->getView()->registerAssetBundle(SelectOtherFieldAsset::class);

        return Craft::$app->view->renderTemplate('sprout-seo/_components/fields/elementmetadata/settings', [
            'settings' => $this->getAttributes(),
            'schemas' => $schemas,
            'schemaSubtypes' => $schemaSubtypes
        ]);
    }

    /**
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return string
     * @throws Exception
     * @throws \Twig_Error_Loader
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        $name = $this->handle;
        $inputId = Craft::$app->view->formatInputId($name);
        $namespaceInputName = Craft::$app->view->namespaceInputName($inputId);
        $namespaceInputId = Craft::$app->view->namespaceInputId($inputId);
        // if comes from the content table
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        $value = new Metadata($value);

        $ogImageElements = [];
        $metaImageElements = [];
        $twitterImageElements = [];

        // Set up our asset fields
        if ($value->optimizedImage) {
            // If validation fails, we need to make sure our asset is just an ID
            $value->optimizedImage = $this->getImageId($value->optimizedImage);
            $asset = Craft::$app->elements->getElementById($value->optimizedImage);
            $metaImageElements = [$asset];
        }

        if ($value->ogImage) {
            $value->ogImage = $this->getImageId($value->ogImage);
            $asset = Craft::$app->elements->getElementById($value->ogImage);
            $ogImageElements = [$asset];
        }

        if ($value->twitterImage) {
            $value->twitterImage = $this->getImageId($value->twitterImage);
            $asset = Craft::$app->elements->getElementById($value->twitterImage);
            $twitterImageElements = [$asset];
        }

        $value['robots'] = OptimizeHelper::prepareRobotsMetadataForSettings($value->robots);

        // Cleanup the namespace around the $name handle
        $name = str_replace('fields[', "", $name);
        $name = rtrim($name, ']');

        $fieldId = 'fields-'.$name.'-field';

        $name = "sproutseo[metadata][$name]";

        $settings = $this->getAttributes();

        Craft::$app->getView()->registerAssetBundle(BaseAsset::class);
        Craft::$app->getView()->registerAssetBundle(OpenGraphAsset::class);
        Craft::$app->getView()->registerAssetBundle(TwitterCardAsset::class);
        Craft::$app->getView()->registerAssetBundle(TagEditorAsset::class);
        Craft::$app->getView()->registerAssetBundle(SchemaAsset::class);

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
     * @param                       $value
     * @param ElementInterface|null $element
     *
     * @return array|mixed|null
     * @throws Exception
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        $metadata = [];
        // when is resaving on all sites comes into array
        if (is_array($value)) {
            $metadata = $value;
        }
        // When is a post request the metadata values comes into the metadata key
        if (isset($value['metadata'])) {
            $metadata = $value['metadata'];
        }
        // On the resave element task the $value comes from the content table as json
        if (is_string($value)) {
            $metadata = json_decode($value, true);
        }

        if (isset($metadata['sproutSeoSettings'])) {
            // removes json value from livepreview
            unset($metadata['sproutSeoSettings']);
        }

        $this->values = $metadata;

        return $this->values;
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
     * @throws \craft\errors\SiteNotFoundException
     */
    public function afterSave(bool $isNew)
    {
        SproutSeo::$app->elementMetadata->resaveElementsIfUsingElementMetadataField($this->id);

        parent::afterSave($isNew);
    }

    /**
     * @param $attributes
     * @param $settings
     * @param $element
     *
     * @return mixed
     *
     * @throws \RuntimeException
     * @throws Exception
     */
    protected function processOptimizedTitle($attributes, $settings, $element)
    {
        $title = null;

        $optimizedTitleFieldSetting = $settings['optimizedTitleField'];

        switch (true) {
            // Element Title
            case ($optimizedTitleFieldSetting === 'elementTitle'):

                $title = $element->title;

                break;

            // Manual Title
            case ($optimizedTitleFieldSetting === 'manually'):

                $title = $attributes['optimizedTitle'] ?? null;

                break;

            // Custom Field
            case (is_numeric($optimizedTitleFieldSetting)):

                $title = $this->getSelectedFieldForOptimizedMetadata($optimizedTitleFieldSetting, $element);

                break;

            // Custom Value
            default:
                $title = Craft::$app->view->renderObjectTemplate($optimizedTitleFieldSetting, $element);

                break;
        }

        $attributes['optimizedTitle'] = $title;

        return $this->setMetaDetailsValues('title', $title, $attributes);
    }

    /**
     * @param $image
     * @return mixed
     */
    private function getImageId($image)
    {
        $imageId = $image;

        if (is_array($image)) {
            $imageId = $image[0];
        }

        return $imageId;
    }

    /**
     * @param $type
     * @param $value
     * @param $attributes
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

    /**
     * @param $attributes
     * @param $settings
     * @param $element
     *
     * @return mixed
     */
    protected function processOptimizedKeywords($attributes, $settings, Element $element)
    {
        $keywords = null;

        $optimizedKeywordsFieldSetting = $settings['optimizedKeywordsField'];

        switch (true) {
            // Manual Keywords
            case ($optimizedKeywordsFieldSetting === 'manually'):

                $keywords = $attributes['optimizedKeywords'] ?? null;

                break;

            // Auto-generate keywords from target field
            case (is_numeric($optimizedKeywordsFieldSetting)):

                $bigKeywords = $this->getSelectedFieldForOptimizedMetadata($optimizedKeywordsFieldSetting, $element);
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

                    if (count($languagePrefixArray) > 0)
                    {
                        $languagePrefix = $languagePrefixArray[0];

                        if (isset($stopWordsMap[$languagePrefix]))
                        {
                            $stopWordsClass = $stopWordsMap[$languagePrefix];
                        }
                    }

                    $stopWords = new $stopWordsClass();

                    try {
                        $textRankApi->setStopWords($stopWords);

                        $rankedKeywords = $textRankApi->getOnlyKeyWords($bigKeywords);
                        $fiveKeywords = array_keys(array_slice($rankedKeywords, 0, 5));
                        $keywords = implode(',', $fiveKeywords);
                    } catch (\RuntimeException $e) {
                        // Cannot detect the language of the text, maybe to short.
                        $keywords = null;
                    }
                }

                break;
        }

        if ($settings['showSearchMeta'] && isset($attributes['keywords']) && $attributes['keywords']){
            $keywords = $attributes['keywords'];
        }

        $attributes['optimizedKeywords'] = $keywords;

        return $attributes;
    }

    /**
     * @param $attributes
     * @param $settings
     * @param $element
     *
     * @return mixed
     * @throws Exception
     */
    protected function processOptimizedDescription($attributes, $settings, $element)
    {
        $description = null;

        $optimizedDescriptionFieldSetting = $settings['optimizedDescriptionField'];

        switch (true) {
            // Manual Description
            case ($optimizedDescriptionFieldSetting === 'manually'):

                $description = $attributes['optimizedDescription'] ?? null;

                break;

            // Custom Description
            case (is_numeric($optimizedDescriptionFieldSetting)):

                $description = $this->getSelectedFieldForOptimizedMetadata($optimizedDescriptionFieldSetting, $element);

                break;

            // Custom Value
            default:

                $description = Craft::$app->view->renderObjectTemplate($optimizedDescriptionFieldSetting, $element);

                break;
        }

        // Just save the first 255 characters (we only output 160...)
        $description = mb_substr(trim($description), 0, 255);
        $attributes['optimizedDescription'] = $description;
        $attributes = $this->setMetaDetailsValues('description', $description, $attributes);

        return $attributes;
    }

    /**
     * @param $attributes
     * @param $settings
     * @param $element
     *
     * @return mixed
     * @throws Exception
     */
    protected function processOptimizedFeatureImage($attributes, $settings, $element)
    {
        $image = null;

        $optimizedImageFieldSetting = $settings['optimizedImageField'];

        switch (true) {
            // Manual Image
            case ($optimizedImageFieldSetting === 'manually'):

                $image = null;

                if (isset($attributes['optimizedImage'][0]) && is_array($attributes['optimizedImage'])) {
                    // the value comes from post data from elementmetada field
                    $image = $attributes['optimizedImage'][0];
                } else if (isset($attributes['optimizedImage']) && $attributes['optimizedImage'] && is_numeric($attributes['optimizedImage'])) {
                    // the value comes from resaveElement task - we store a numeric value on the table
                    $image = $attributes['optimizedImage'];
                }

                break;

            // Custom Image Field
            case (is_numeric($optimizedImageFieldSetting)):

                $image = $this->getSelectedFieldForOptimizedMetadata($optimizedImageFieldSetting, $element);

                break;

            // Custom Value
            default:

                $image = Craft::$app->view->renderObjectTemplate($optimizedImageFieldSetting, $element);

                break;
        }

        $attributes['optimizedImage'] = $image;

        if (isset($attributes['ogImage'][0]) && is_array($attributes['ogImage'])) {
            // the value comes from post data from elementmetada field
            $attributes['ogImage'] = $attributes['ogImage'][0];
        } else if (isset($attributes['ogImage']) && $attributes['ogImage'] && is_numeric($attributes['ogImage'])) {
            // the value comes from resaveElement task - we store a numeric value on the table
            $attributes['ogImage'] = $attributes['ogImage'];
        } else {
            $attributes['ogImage'] = $image;
        }

        if (isset($attributes['twitterImage'][0]) && is_array($attributes['twitterImage'])) {
            $attributes['twitterImage'] = $attributes['twitterImage'][0];
        } else if (isset($attributes['twitterImage']) && $attributes['twitterImage'] && is_numeric($attributes['twitterImage'])) {
            // the value comes from resaveElement task - we store a numeric value on the table
            $attributes['twitterImage'] = $attributes['twitterImage'];
        } else {
            $attributes['twitterImage'] = $image;
        }

        return $attributes;
    }

    /**
     * @param $attributes
     * @param $settings
     *
     * @return mixed
     */
    protected function processMainEntity($attributes, $settings)
    {
        $attributes['schemaTypeId'] = $settings['schemaTypeId'];
        $attributes['schemaOverrideTypeId'] = $settings['schemaOverrideTypeId'];

        return $attributes;
    }

    /**
     * Make sure our Meta Details blocks behave as we need them to.
     *
     * Can be triggered via:
     * - Save Element
     * - ResaveElements via saving of Element Metadata Field
     * - ResaveElements via save Field Layout
     *
     * Handles several scenarios:
     * - New Metadata, Existing Metadata
     * - Meta Details Blocks - enabled, partially enabled, disabled
     *
     * @param $attributes
     *
     * @return mixed
     */
    protected function processMetaDetails($attributes)
    {
        return $attributes;
    }

    /**
     * @param $fieldId
     * @param $element
     *
     * @return null
     */
    private function getSelectedFieldForOptimizedMetadata($fieldId, $element)
    {
        $value = null;

        if (is_numeric($fieldId)) {
            /**
             * @var Field $field
             */
            $field = Craft::$app->fields->getFieldById($fieldId);

            // Does the field exist on the element?
            if ($field) {
                if (isset($_POST['fields'][$field->handle])) {
                    if (get_class($field) === Assets::class) {
                        $value = (!empty($_POST['fields'][$field->handle]) ? $_POST['fields'][$field->handle][0] : null);
                    } else {
                        $value = $_POST['fields'][$field->handle];
                    }
                } else {
                    // Resave elements scenario
                    if (isset($element->{$field->handle})) {
                        $elementValue = $element->{$field->handle};

                        if (get_class($field) === Assets::class) {
                            $value = isset($elementValue[0]) ? $elementValue[0]->id : null;
                        } else {
                            $value = $elementValue;
                        }
                    }
                }
            }
        }

        return $value;
    }

    /**
     * @param $fields
     * @param $element
     *
     * @return array
     * @throws Exception
     */
    protected function getMetadataFieldValues($fields, $element)
    {
        $settings = $this->getAttributes();

        // Get instance of our Element Metadata model if a call comes from a ResaveElements task
        // Get existing or new MetadataModel
        $this->metadata = new Metadata($fields);
        $attributes = [];

        // Grab all the other Sprout SEO fields.
        if ($fields) {
            if (isset($fields['robots'])) {
                $fields['robots'] = OptimizeHelper::prepareRobotsMetadataValue($fields['robots']);
            }

            $attributes = array_merge($attributes, $fields);
        }

        // Meta Details needs to go first
        $attributes = $this->processMetaDetails($attributes);
        $attributes = $this->processOptimizedTitle($attributes, $settings, $element);
        $attributes = $this->processOptimizedDescription($attributes, $settings, $element);
        $attributes = $this->processOptimizedKeywords($attributes, $settings, $element);
        $attributes = $this->processOptimizedFeatureImage($attributes, $settings, $element);
        $attributes = $this->processMainEntity($attributes, $settings);

        $this->metadata->setAttributes($attributes, false);

        $this->metadata = OptimizeHelper::updateOptimizedAndAdvancedMetaValues($this->metadata);

        if (isset($attributes['canonical']) && $attributes['canonical']) {
            $this->metadata->canonical = $attributes['canonical'];
        }

        // Overwrite any values we have from our existing model with the values from our attributes
        return array_intersect_key($this->metadata->getAttributes(), $attributes);
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
}