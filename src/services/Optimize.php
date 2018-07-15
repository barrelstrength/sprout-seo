<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\services;

use barrelstrength\sproutseo\schema\WebsiteIdentityPersonSchema;
use barrelstrength\sproutseo\schema\WebsiteIdentityPlaceSchema;
use barrelstrength\sproutseo\schema\WebsiteIdentityWebsiteSchema;
use barrelstrength\sproutseo\schema\WebsiteIdentityOrganizationSchema;
use barrelstrength\sproutseo\enums\MetadataLevels;
use barrelstrength\sproutseo\models\Globals;
use barrelstrength\sproutseo\models\Metadata as MetadataModel;

use barrelstrength\sproutseo\helpers\OptimizeHelper;
use barrelstrength\sproutseo\models\Metadata;

use barrelstrength\sproutseo\SproutSeo;
use barrelstrength\sproutseo\models\Settings;
use craft\base\Element;

use craft\models\Site;
use DateTime;
use Craft;
use yii\base\Component;

class Optimize extends Component
{
    /**
     * Sprout SEO Globals data
     *
     * @var Globals $globals
     */
    public $globals;

    /**
     * The first Element Metadata field Metadata from the context
     *
     * @var Metadata $elementMetadata
     */
    public $elementMetadata;

    /**
     * @var MetadataModel $prioritizedMetadataModel
     */
    public $prioritizedMetadataModel;

    /**
     * @var MetadataModel $templateMetadata
     */
    public $templateMetadata = [];

    /**
     * Add values to the master $this->templateMetadata array
     *
     * @param array $meta
     */
    public function updateMeta($meta)
    {
        if (count($meta)) {
            foreach ($meta as $key => $value) {
                $this->templateMetadata[$key] = $value;
            }
        }
    }

    /**
     * Get all metadata (Meta Tags and Structured Data) for the page
     *
     * @param $context
     *
     * @return array|null|string
     * @throws \Twig_Error_Loader
     * @throws \craft\errors\SiteNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\web\ServerErrorHttpException
     */
    public function getMetadataViaContext(&$context)
    {
        $site = $context['currentSite'] ?? Craft::$app->getSites()->currentSite;

        $element = SproutSeo::$app->sitemaps->getElementViaContext($context);

        $this->elementMetadata = SproutSeo::$app->elementMetadata->getElementMetadata($element);

        return $this->getMetadata($element, $site);
    }

    /**
     * @param      $element
     * @param      $site
     * @param bool $render
     *
     * @return array|null|string
     * @throws \Twig_Error_Loader
     * @throws \craft\errors\SiteNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\web\ServerErrorHttpException
     */
    public function getMetadata(Element $element, $site, $render = true)
    {
        /**
         * @var Settings $settings
         */
        $settings = Craft::$app->plugins->getPlugin('sprout-seo')->getSettings();

        $this->globals = SproutSeo::$app->globalMetadata->getGlobalMetadata($site);
        $this->prioritizedMetadataModel = $this->getPrioritizedMetadataModel($element, $site);

        $output = null;

        $metadata = [
            'globals' => $this->globals,
            'meta' => $this->prioritizedMetadataModel->getMetaTagData(),
            'schema' => $this->getStructuredData($element)
        ];

        if ($render === false) {
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
     * Prioritize our meta data
     * ------------------------------------------------------------
     *
     * Loop through and select the highest ranking value for each attribute in our Metadata
     *
     * 4) Blank
     * 3) Global Metadata
     * 2) Element Metadata
     * 1) Template Metadata
     *
     * @param      $element
     * @param Site $site
     *
     * @return Metadata|mixed
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\ServerErrorHttpException
     */
    public function getPrioritizedMetadataModel($element, $site = null)
    {
        $metaLevels = [
            MetadataLevels::GlobalMetadata,
            MetadataLevels::ElementMetadata,
            MetadataLevels::TemplateMetadata
        ];

        $prioritizedMetadataModel = new Metadata();

        foreach ($metaLevels as $level) {

            $overrideInfo = [];

            switch ($level) {
                case MetadataLevels::GlobalMetadata:
                    {
                        $overrideInfo = $this->globals->meta;
                        break;
                    }
                case MetadataLevels::ElementMetadata:
                    {
                        if ($this->elementMetadata){
                            $this->elementMetadata->ogLocale = $site->language;

                            // Default to the current URL, if no overrides exist
                            $this->elementMetadata->canonical = OptimizeHelper::prepareCanonical($this->elementMetadata);
                            $this->elementMetadata->ogUrl = OptimizeHelper::prepareCanonical($this->elementMetadata);
                            $this->elementMetadata->twitterUrl = OptimizeHelper::prepareCanonical($this->elementMetadata);

                            $overrideInfo = $this->elementMetadata->getAttributes();
                        }

                        break;
                    }
                case MetadataLevels::TemplateMetadata:
                    {
                        $overrideInfo = $this->templateMetadata;

                        // Assume our canonical URL is the current URL unless there is a codeOverride
                        $prioritizedMetadataModel->canonical = OptimizeHelper::prepareCanonical($prioritizedMetadataModel);
                        $prioritizedMetadataModel->ogUrl = OptimizeHelper::prepareCanonical($prioritizedMetadataModel);
                        $prioritizedMetadataModel->twitterUrl = OptimizeHelper::prepareCanonical($prioritizedMetadataModel);

                        break;
                    }
            }

            $metadataModel = new Metadata($overrideInfo);
            $metadataModel->keywords = $metadataModel->optimizedKeywords ?? $metadataModel->keywords;

            $prioritizedMetadataModel = $this->getPrioritizedValues($prioritizedMetadataModel, $metadataModel);
        }

        // Remove the ogAuthor value if we don't have an article
        if ($prioritizedMetadataModel->ogType != 'article') {
            $prioritizedMetadataModel->ogAuthor = null;
            $prioritizedMetadataModel->ogPublisher = null;
        } else {
            $prioritizedMetadataModel->ogDateCreated = null;
            $prioritizedMetadataModel->ogDateUpdated = null;
            $prioritizedMetadataModel->ogExpiryDate = null;

            if ($element->dateCreated !== null && $element->dateCreated) {
                $prioritizedMetadataModel->ogDateCreated = $element->dateCreated->format(DateTime::ISO8601);
            }

            if ($element->dateUpdated !== null && $element->dateUpdated) {
                $prioritizedMetadataModel->ogDateUpdated = $element->dateUpdated->format(DateTime::ISO8601);
            }

            /** @todo - this should be delegated to the Url-Enabled Element integration. It's not common to all elements. */
            /** @noinspection PhpUndefinedFieldInspection */
            if ($element->expiryDate !== null && $element->expiryDate) {
                /** @noinspection PhpUndefinedFieldInspection */
                $prioritizedMetadataModel->ogExpiryDate = $element->expiryDate->format(DateTime::ISO8601);
            }
        }

        $prioritizedMetadataModel->title = OptimizeHelper::prepareAppendedTitleValue(
            $prioritizedMetadataModel
        );

        $prioritizedMetadataModel->robots = OptimizeHelper::prepareRobotsMetadataValue($prioritizedMetadataModel->robots);

        // let's just prepare assets for final metadatamodel
        OptimizeHelper::prepareAssetUrls($prioritizedMetadataModel);

        // Trim descriptions to maxMetaDescriptionLength or 160 characters
        $descriptionLength = SproutSeo::$app->settings->getDescriptionLength();

        $prioritizedMetadataModel->optimizedDescription = mb_substr($prioritizedMetadataModel->optimizedDescription, 0, $descriptionLength);
        $prioritizedMetadataModel->description = mb_substr($prioritizedMetadataModel->description, 0, $descriptionLength);
        $prioritizedMetadataModel->ogDescription = mb_substr($prioritizedMetadataModel->ogDescription, 0, $descriptionLength);
        $prioritizedMetadataModel->twitterDescription = mb_substr($prioritizedMetadataModel->twitterDescription, 0, $descriptionLength);

        return $prioritizedMetadataModel;
    }

    public function getStructuredData($element)
    {
        $schema = [];
        $websiteIdentity = [
            'Person' => WebsiteIdentityPersonSchema::class,
            'Organization' => WebsiteIdentityOrganizationSchema::class
        ];

        $identityType = $this->globals->identity['@type'] ?? null;

        // Website Identity Schema
        if (isset($websiteIdentity[$identityType])) {
            // Determine if we have an Organization or Person Schema Type
            $schemaModel = $websiteIdentity[$identityType];

            $identitySchema = new $schemaModel();
            $identitySchema->addContext = true;

            $identitySchema->globals = $this->globals;
            $identitySchema->prioritizedMetadataModel = $this->prioritizedMetadataModel;

            if ($element !== null) {
                $identitySchema->element = $element;
            }

            $schema['websiteIdentity'] = $identitySchema;
        }

        // Website Identity Website
        if (isset($this->globals->identity['name'])) {
            $websiteSchema = new WebsiteIdentityWebsiteSchema();
            $websiteSchema->addContext = true;

            $websiteSchema->globals = $this->globals;
            $websiteSchema->prioritizedMetadataModel = $this->prioritizedMetadataModel;

            if ($element !== null) {
                $websiteSchema->element = $element;
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

            if ($element !== null) {
                $placeSchema->element = $element;
            }

            $schema['place'] = $placeSchema;
        }

        $schema['mainEntity'] = $this->getMainEntityStructuredData($element);

        return $schema;
    }

    /**
     * @param Element $element
     *
     * @return mixed|null
     */
    public function getMainEntityStructuredData(Element $element)
    {
        $schema = null;

        if ($this->prioritizedMetadataModel) {
            $schemaUniqueKey = $this->prioritizedMetadataModel->schemaTypeId;
            if ($schemaUniqueKey && $element !== null) {
                $schema = SproutSeo::$app->schema->getSchemaByUniqueKey($schemaUniqueKey);
                $schema->attributes = $this->prioritizedMetadataModel->getAttributes();
                $schema->addContext = true;
                $schema->isMainEntity = true;

                $schema->globals = $this->globals;
                $schema->element = $element;
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
     * @param $prioritizedMetadataModel
     * @param $metadataModel
     *
     * @return mixed
     */
    protected function getPrioritizedValues(Metadata $prioritizedMetadataModel, $metadataModel)
    {
        foreach ($prioritizedMetadataModel->getAttributes() as $attribute => $value) {
            // Test for a value on each of our models in their order of priority
            if ($metadataModel->{$attribute}) {
                $prioritizedMetadataModel->{$attribute} = $metadataModel->{$attribute};
            }
            // Make sure our schema type and override are all or nothing
            // if we find the $metadataModel doesn't have a value for schemaOverrideTypeId
            // then we should make sure the $prioritizedMetadataModel also has a null value
            // otherwise we still keep our lower level value
            if ($attribute === 'schemaOverrideTypeId' &&
                $metadataModel->schemaTypeId != null &&
                $metadataModel->{$attribute} == null
            ) {
                $prioritizedMetadataModel->{$attribute} = null;
            }

            // Make sure all our strings are trimmed
            if (is_string($prioritizedMetadataModel->{$attribute})) {
                $prioritizedMetadataModel->{$attribute} = trim($prioritizedMetadataModel->{$attribute});
            }
        }

        return $prioritizedMetadataModel;
    }
}
