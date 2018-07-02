<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\services;

use barrelstrength\sproutseo\events\RegisterSchemasEvent;

use barrelstrength\sproutseo\schema\ContactPointSchema;
use barrelstrength\sproutseo\schema\CreativeWorkSchema;
use barrelstrength\sproutseo\schema\EventSchema;
use barrelstrength\sproutseo\schema\GeoSchema;
use barrelstrength\sproutseo\schema\ImageObjectSchema;
use barrelstrength\sproutseo\schema\IntangibleSchema;
use barrelstrength\sproutseo\schema\MainEntityOfPageSchema;
use barrelstrength\sproutseo\schema\OrganizationSchema;
use barrelstrength\sproutseo\schema\PersonSchema;
use barrelstrength\sproutseo\schema\PlaceSchema;
use barrelstrength\sproutseo\schema\PostalAddressSchema;
use barrelstrength\sproutseo\schema\ProductSchema;
use barrelstrength\sproutseo\schema\ThingSchema;
use barrelstrength\sproutseo\schema\WebsiteIdentityPersonSchema;
use barrelstrength\sproutseo\schema\WebsiteIdentityPlaceSchema;
use barrelstrength\sproutseo\schema\WebsiteIdentityWebsiteSchema;
use barrelstrength\sproutseo\schema\WebsiteIdentityOrganizationSchema;
use barrelstrength\sproutseo\enums\MetadataLevels;
use barrelstrength\sproutseo\models\Globals;
use barrelstrength\sproutseo\models\Metadata as MetadataModel;
use barrelstrength\sproutseo\fields\ElementMetadata;
use barrelstrength\sproutseo\helpers\SproutSeoOptimizeHelper;
use barrelstrength\sproutseo\models\Metadata;
use barrelstrength\sproutseo\models\UrlEnabledSection;
use barrelstrength\sproutseo\SproutSeo;
use barrelstrength\sproutseo\base\Schema;
use barrelstrength\sproutseo\models\Settings;
use craft\base\Element;
use craft\base\Field;
use DateTime;
use Craft;
use yii\base\Component;

class Optimize extends Component
{
    const EVENT_REGISTER_SCHEMAS = 'registerSchemasEvent';

    public $rawMetadata = false;

    /**
     * All registered Schema Types
     *
     * @var array
     */
    protected $schemaTypes = [];

    /**
     * All instantiated Schema Types indexed by class
     *
     * @var Schema[]
     */
    protected $schemas = [];

    /**
     * Sprout SEO Globals data
     *
     * @var Globals $globals
     */
    public $globals;

    /**
     * The active Section integration with Section and Element info
     *
     * $urlEnabledSection->element will have the element that matches
     * the matchedElementVariable from the $context
     *
     * @var UrlEnabledSection $urlEnabledSection
     */
    public $urlEnabledSection;

    /**
     * The first element metadata field from the context
     *
     * @var ElementMetadata $metadataField
     */
    public $metadataField;

    /**
     * @var MetadataModel $prioritizedMetadataModel
     */
    public $prioritizedMetadataModel;

    /**
     * @var MetadataModel $codeMetadata
     */
    public $codeMetadata = [];

    /**
     * @var
     */
    public $codeSection;

    /**
     * Current siteId
     *
     * @var
     */
    public $siteId;

    /**
     * @return array
     */
    public function getSchemasTypes()
    {
        $schemas = [
            WebsiteIdentityOrganizationSchema::class,
            WebsiteIdentityPersonSchema::class,
            WebsiteIdentityWebsiteSchema::class,
            WebsiteIdentityPlaceSchema::class,
            ContactPointSchema::class,
            ImageObjectSchema::class,
            MainEntityOfPageSchema::class,
            PostalAddressSchema::class,
            GeoSchema::class,
            ThingSchema::class,
            CreativeWorkSchema::class,
            EventSchema::class,
            IntangibleSchema::class,
            OrganizationSchema::class,
            PersonSchema::class,
            PlaceSchema::class,
            ProductSchema::class
        ];

        $event = new RegisterSchemasEvent([
            'schemas' => $schemas
        ]);

        $this->trigger(Optimize::EVENT_REGISTER_SCHEMAS, $event);

        foreach ($event->schemas as $schema) {
            $this->schemaTypes[] = $schema;
        }

        return $this->schemaTypes;
    }

    /**
     * @return Schema[]
     */
    public function getSchemas()
    {
        $schemaTypes = $this->getSchemasTypes();

        foreach ($schemaTypes as $schemaClass) {
            $schema = new $schemaClass();
            $this->schemas[$schemaClass] = $schema;
        }

        uasort($this->schemas, function($a, $b) {
            /**
             * @var $a Schema
             * @var $b Schema
             */
            return $a->getName() <=> $b->getName();
        });

        return $this->schemas;
    }

    /**
     * Returns a list of available schema maps for display in a Main Entity select field
     *
     * @return array
     */
    public function getSchemaOptions()
    {
        $options = [];

        foreach ($this->schemas as $uniqueKey => $instance) {
            $options[] = [
                'value' => $uniqueKey,
                'label' => $instance->getName()
            ];
        }

        return $options;
    }

    /**
     * Returns a schema map instance (based on $uniqueKey) or $default
     *
     * @param string $uniqueKey
     * @param null   $default
     *
     * @return mixed|null
     */
    public function getSchemaByUniqueKey($uniqueKey, $default = null)
    {
        $this->getSchemas();
        return array_key_exists($uniqueKey, $this->schemas) ? $this->schemas[$uniqueKey] : $default;
    }

    /**
     * Add values to the master $this->codeMetadata array
     *
     * @param array $meta
     */
    public function updateMeta($meta)
    {
        if (count($meta)) {
            foreach ($meta as $key => $value) {
                if ($key == 'section' || $key == 'default') {
                    if ($key == 'default') {
                        Craft::$app->deprecator->log('meta default key deprecated', 'craft.sproutseo.meta `default` key has been deprecated. Use `section` key instead: {% do craft.sproutseo.meta( section: "'.$value.'"") %}');
                    }

                    $this->codeSection = $value;
                } else {
                    $this->codeMetadata[$key] = $value;
                }
            }
        }
    }

    /**
     * Get all metadata (Meta Tags and Structured Data) for the page
     *
     * @param $context
     *
     * @return array|null|string
     * @throws \Exception
     * @throws \yii\base\Exception
     * @throws \yii\web\ServerErrorHttpException
     */
    public function getMetadata(&$context)
    {
        $output = null;

        /**
         * @var Settings $settings
         */
        $settings = Craft::$app->plugins->getPlugin('sprout-seo')->getSettings();
        $this->siteId = $context['currentSite']->id ?? Craft::$app->getSites()->currentSite->id;

        $this->globals = SproutSeo::$app->globalMetadata->getGlobalMetadata($this->siteId);
        $this->urlEnabledSection = SproutSeo::$app->sitemaps->getUrlEnabledSectionsViaContext($context);
        $this->metadataField = $this->getMetadataField($this->urlEnabledSection->element);
        $this->prioritizedMetadataModel = $this->getPrioritizedMetadataModel($this->siteId);

        $metadata = [
            'globals' => $this->globals,
            'meta' => $this->prioritizedMetadataModel->getMetaTagData(),
            'schema' => $this->getStructuredData()
        ];

        if ($this->rawMetadata == true) {
            return $metadata;
        }

        // Output metadata
        if ($settings->enableMetadataRendering) {
            $output = $this->renderMetadata($metadata);
        }

        // Add metadata variable to Twig context
        if ($settings->metadataVariable) {
            $context[$settings->metadataVariable] = $metadata;
        }

        return $output;
    }

    /**
     * Find any element with the getContent function and fetch the first ElementMetadata Field on the layout
     *
     * @return Metadata|null
     */
    public function getMetadataField(Element $element = null)
    {
        if (!$element)
        {
            return null;
        }

        $fields = $element->getFieldLayout()->getFields();

        /**
         * Get our ElementMetadata Field
         *
         * @var Field $field
         */
        foreach ($fields as $field) {
            if (get_class($field) == ElementMetadata::class) {
                if (isset($element->{$field->handle})) {
                    $metadata = $element->{$field->handle};
                    return new Metadata($metadata);
                }
            }
        }

        return null;
    }

    /**
     * Prioritize our meta data
     * ------------------------------------------------------------
     *
     * Loop through and select the highest ranking value for each attribute in our Metadata
     *
     * 1) Code Metadata
     * 2) Element Metadata
     * 3) Global Metadata
     * 4) Blank
     *
     * @param int siteId
     *
     * @return Metadata
     * @throws \Exception
     * @throws \yii\base\Exception
     * @throws \yii\web\ServerErrorHttpException
     */
    public function getPrioritizedMetadataModel($siteId = null)
    {
        $metaLevels = MetadataLevels::getConstants();

        $this->globals = $this->globals ?? SproutSeo::$app->globalMetadata->getGlobalMetadata($siteId);

        $prioritizedMetadataLevels = [];

        foreach ($metaLevels as $key => $metaLevel) {
            $prioritizedMetadataLevels[$metaLevel] = null;
        }

        $prioritizedMetadataModel = new Metadata();

        foreach ($prioritizedMetadataLevels as $level => $model) {
            $metadataModel = new Metadata();
            $codeMetadata = $this->getCodeMetadata($level);

            // Assume our canonical URL is the current URL unless there is a codeOverride
            if ($level == MetadataLevels::CodeMetadata) {
                $prioritizedMetadataModel->canonical = SproutSeoOptimizeHelper::prepareCanonical($prioritizedMetadataModel);
                $prioritizedMetadataModel->ogUrl = SproutSeoOptimizeHelper::prepareCanonical($prioritizedMetadataModel);
                $prioritizedMetadataModel->twitterUrl = SproutSeoOptimizeHelper::prepareCanonical($prioritizedMetadataModel);
            }

            $metadataModel = $metadataModel->setMeta($level, $codeMetadata);

            $prioritizedMetadataLevels[$level] = $metadataModel;
            $metadataModel->keywords = null !== $metadataModel->optimizedKeywords ? $metadataModel->optimizedKeywords : $metadataModel->keywords;

            foreach ($prioritizedMetadataModel->getAttributes() as $key => $value) {
                // Test for a value on each of our models in their order of priority
                if ($metadataModel->{$key}) {
                    $prioritizedMetadataModel->{$key} = $metadataModel->{$key};
                }
                // Make sure our schema type and override are all or nothing
                // if we find the $metadataModel doesn't have a value for schemaOverrideTypeId
                // then we should make sure the $prioritizedMetadataModel also has a null value
                // otherwise we still keep our lower level value
                if ($key == 'schemaOverrideTypeId' &&
                    $metadataModel->schemaTypeId != null &&
                    $metadataModel->{$key} == null
                ) {
                    $prioritizedMetadataModel->{$key} = null;
                }

                // Make sure all our strings are trimmed
                if (is_string($prioritizedMetadataModel->{$key})) {
                    $prioritizedMetadataModel->{$key} = trim($prioritizedMetadataModel->{$key});
                }
            }
        }

        // Remove the ogAuthor value if we don't have an article
        if ($prioritizedMetadataModel->ogType != 'article') {
            $prioritizedMetadataModel->ogAuthor = null;
            $prioritizedMetadataModel->ogPublisher = null;
        } else {
            $prioritizedMetadataModel->ogDateCreated = null;
            $prioritizedMetadataModel->ogDateUpdated = null;
            $prioritizedMetadataModel->ogExpiryDate = null;

            if ($this->urlEnabledSection->element->dateCreated !== null && $this->urlEnabledSection->element->dateCreated) {
                $prioritizedMetadataModel->ogDateCreated = $this->urlEnabledSection->element->dateCreated->format(DateTime::ISO8601);
            }

            if ($this->urlEnabledSection->element->dateUpdated !== null && $this->urlEnabledSection->element->dateUpdated) {
                $prioritizedMetadataModel->ogDateUpdated = $this->urlEnabledSection->element->dateUpdated->format(DateTime::ISO8601);
            }

            if ($this->urlEnabledSection->element->expiryDate !== null && $this->urlEnabledSection->element->expiryDate) {
                $prioritizedMetadataModel->ogExpiryDate = $this->urlEnabledSection->element->expiryDate->format(DateTime::ISO8601);
            }
        }

        $prioritizedMetadataModel->title = SproutSeoOptimizeHelper::prepareAppendedTitleValue(
            $prioritizedMetadataModel
        );

        $prioritizedMetadataModel->robots = SproutSeoOptimizeHelper::prepareRobotsMetadataValue($prioritizedMetadataModel->robots);

        // let's just prepare assets for final metadatamodel
        SproutSeoOptimizeHelper::prepareAssetUrls($prioritizedMetadataModel);

        // Trim descriptions to maxMetaDescriptionLength or 160 characters
        $descriptionLength = SproutSeo::$app->settings->getDescriptionLength();

        $prioritizedMetadataModel->optimizedDescription = mb_substr($prioritizedMetadataModel->optimizedDescription, 0, $descriptionLength);
        $prioritizedMetadataModel->description = mb_substr($prioritizedMetadataModel->description, 0, $descriptionLength);
        $prioritizedMetadataModel->ogDescription = mb_substr($prioritizedMetadataModel->ogDescription, 0, $descriptionLength);
        $prioritizedMetadataModel->twitterDescription = mb_substr($prioritizedMetadataModel->twitterDescription, 0, $descriptionLength);

        return $prioritizedMetadataModel;
    }

    public function getStructuredData()
    {
        $schema = [];
        $websiteIdentity = [
            'Person' => WebsiteIdentityPersonSchema::class,
            'Organization' => WebsiteIdentityOrganizationSchema::class
        ];
        //$output       = null;
        $identityType = $this->globals->identity['@type'];

        // Website Identity Schema
        if (isset($websiteIdentity[$identityType])) {
            // Determine if we have an Organization or Person Schema Type
            $schemaModel = $websiteIdentity[$identityType];

            $identitySchema = new $schemaModel();
            $identitySchema->addContext = true;

            $identitySchema->globals = $this->globals;
            $identitySchema->prioritizedMetadataModel = $this->prioritizedMetadataModel;

            if ($this->urlEnabledSection->element !== null) {
                $identitySchema->element = $this->urlEnabledSection->element;
            }

            $schema['websiteIdentity'] = $identitySchema;
        }

        // Website Identity Website
        if ($this->globals->identity['name']) {
            $websiteSchema = new WebsiteIdentityWebsiteSchema();
            $websiteSchema->addContext = true;

            $websiteSchema->globals = $this->globals;
            $websiteSchema->prioritizedMetadataModel = $this->prioritizedMetadataModel;

            if ($this->urlEnabledSection->element !== null) {
                $websiteSchema->element = $this->urlEnabledSection->element;
            }

            $schema['website'] = $websiteSchema;
        }

        $identity = $this->globals->identity;

        // Website Identity Place
        if (isset($identity['addressId']) && $identity['addressId']) {
            $placeSchema = new WebsiteIdentityPlaceSchema();
            $placeSchema->addContext = true;

            $placeSchema->globals = $this->globals;
            $placeSchema->prioritizedMetadataModel = $this->prioritizedMetadataModel;

            if ($this->urlEnabledSection->element !== null) {
                $placeSchema->element = $this->urlEnabledSection->element;
            }

            $schema['place'] = $placeSchema;
        }

        $schema['mainEntity'] = $this->getMainEntityStructuredData();

        return $schema;
    }

    /**
     * @return string
     */
    public function getMainEntityStructuredData()
    {
        $schema = null;

        if ($this->prioritizedMetadataModel) {
            $schemaUniqueKey = $this->prioritizedMetadataModel->schemaTypeId;
            if ($schemaUniqueKey && $this->urlEnabledSection->element !== null) {
                $schema = $this->getSchemaByUniqueKey($schemaUniqueKey);
                $schema->attributes = $this->prioritizedMetadataModel->getAttributes();
                $schema->addContext = true;
                $schema->isMainEntity = true;

                $schema->globals = $this->globals;
                $schema->element = $this->urlEnabledSection->element;
                $schema->prioritizedMetadataModel = $this->prioritizedMetadataModel;
            }
        }

        return $schema;
    }

    /**
     * Get all metadata (Meta Tags and Structured Data) for the page
     *
     * @param $metadata
     *
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function renderMetadata($metadata)
    {
        $sproutSeoTemplatesPath = Craft::getAlias('@sproutbase/app/seo/');

        Craft::$app->view->setTemplatesPath($sproutSeoTemplatesPath);

        $output = Craft::$app->view->renderTemplate('templates/_special/metadata', [
            'metadata' => $metadata
        ]);

        Craft::$app->view->setTemplatesPath(Craft::$app->path->getSiteTemplatesPath());

        return $output;
    }

    /**
     * Store our codeMetadata in a place so we can access when we need to
     *
     * @todo - Rename method
     *         This is named 'getCodeMetadata' but also handles overrides for Section and Element data.
     *
     * @param null $type
     *
     * @return array|Metadata
     */
    public function getCodeMetadata($type = null)
    {
        $response = [];

        switch ($type) {
            case MetadataLevels::ElementMetadata:
                if ($this->urlEnabledSection->element !== null) {
                    if ($this->urlEnabledSection->element->id !== null) {
                        $response = [
                            'metadataField' => $this->metadataField,
                            'contextElement' => $this->urlEnabledSection->element
                        ];
                    }
                }
                break;

            case MetadataLevels::CodeMetadata:
                $response = $this->codeMetadata;
                break;
        }

        return $response;
    }

    /**
     * Returns the url enable section given the variable id
     *
     * @param $variableIdValue
     * @param $variableNameId
     *
     * @return array
     * @throws \craft\errors\SiteNotFoundException
     */
    public function getContextByElementVariable($variableIdValue, $variableNameId)
    {
        $response = [];

        $registeredUrlEnabledSectionsTypes = SproutSeo::$app->urlEnabledSections->getRegisteredUrlEnabledSectionsEvent();

        foreach ($registeredUrlEnabledSectionsTypes as $plugin => $urlEnabledSectionType) {
            // Let's get the optimized metadata model
            $idVariableName = $urlEnabledSectionType->getIdVariableName();

            if ($idVariableName == $variableNameId) {
                // example: entry, category, etc.
                $elementType = $urlEnabledSectionType->getMatchedElementVariable();
                $site = Craft::$app->getSites()->getPrimarySite();
                $elementById = Craft::$app->elements->getElementById($variableIdValue, $urlEnabledSectionType->getElementType(), $site->id);

                if ($elementById) {
                    $response = [
                        $elementType => $elementById
                    ];

                    return $response;
                }
            }
        }

        return $response;
    }

    /**
     * Returns array of URL Enabled Section types and the name of Element ID associated with each
     *
     * @todo - rename this getElementIdName() or something like that
     *
     * @return array
     */
    public function getVariableIdNames()
    {
        $registeredUrlEnabledSectionsTypes = SproutSeo::$app->urlEnabledSections->getRegisteredUrlEnabledSectionsEvent();

        $variableTypes = [];

        foreach ($registeredUrlEnabledSectionsTypes as $urlEnabledSectionType) {
            $idVariableName = $urlEnabledSectionType->getIdVariableName();
            $variableTypes[] = $idVariableName;
        }

        return $variableTypes;
    }
}
