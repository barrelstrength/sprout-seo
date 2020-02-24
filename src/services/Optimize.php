<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\services;

use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutseo\fields\ElementMetadata;
use barrelstrength\sproutseo\models\Globals;
use barrelstrength\sproutseo\models\Metadata;
use barrelstrength\sproutseo\models\Settings;
use barrelstrength\sproutseo\schema\WebsiteIdentityOrganizationSchema;
use barrelstrength\sproutseo\schema\WebsiteIdentityPersonSchema;
use barrelstrength\sproutseo\schema\WebsiteIdentityPlaceSchema;
use barrelstrength\sproutseo\schema\WebsiteIdentityWebsiteSchema;
use barrelstrength\sproutseo\SproutSeo;
use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\elements\Asset;
use craft\errors\SiteNotFoundException;
use craft\helpers\UrlHelper;
use craft\models\Site;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Component;
use yii\base\Exception;
use yii\base\ExitException;
use yii\base\InvalidConfigException;

/**
 *
 * @property string $uri
 * @property Site   $matchedSite
 */
class Optimize extends Component
{
    /**
     * Sprout SEO Globals data
     *
     * @var Globals $globals
     */
    public $globals;

    /**
     * @var ElementInterface|Element
     */
    public $element;

    /**
     * The first Element Metadata field Metadata from the context
     *
     * @var ElementMetadata $elementMetadataField
     */
    public $elementMetadataField;

    /**
     * @var Metadata $prioritizedMetadataModel
     */
    public $prioritizedMetadataModel;

    /**
     * @var array $templateMetadata
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
     * @throws Throwable
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws SiteNotFoundException
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function getMetadataViaContext(&$context)
    {
        $uri = $this->getUri();
        $site = $this->getMatchedSite();
        $this->setMatchedElement($uri, $site->id);

        return $this->getMetadata($site, true, $context);
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        $request = Craft::$app->getRequest();
        $uri = '/';
        if (!$request->getIsConsoleRequest()) {
            try {
                $uri = $request->getPathInfo();
            } catch (InvalidConfigException $e) {
                Craft::error($e->getMessage(), __METHOD__);
            }
        }

        return $uri;
    }

    /**
     * @return Site
     */
    public function getMatchedSite(): Site
    {
        $site = Craft::$app->getSites()->currentSite
            ?? Craft::$app->getSites()->primarySite;

        return $site;
    }

    /**
     * Set the element that matches the $uri
     *
     * @param string   $uri
     * @param int|null $siteId
     */
    public function setMatchedElement(string $uri, int $siteId = null)
    {
        $this->element = null;
        $uri = trim($uri, '/');
        /** @var Element $element */
        $element = Craft::$app->getElements()->getElementByUri($uri, $siteId);
        if ($element && ($element->uri !== null)) {
            $this->element = $element;
        }
    }

    /**
     * @param                     $site
     * @param bool                $render
     * @param bool                $context
     *
     * @return array|null|string
     * @throws Throwable
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getMetadata($site = null, $render = true, &$context = null)
    {
        /** @var SproutSeo $plugin */
        $plugin = Craft::$app->plugins->getPlugin('sprout-seo');
        /** @var Settings $settings */
        $settings = $plugin->getSettings();

        $this->globals = SproutSeo::$app->globalMetadata->getGlobalMetadata($site);
        $this->prioritizedMetadataModel = $this->getPrioritizedMetadataModel($this->element, $site);

        $output = null;

        $metadata = [
            'globals' => $this->globals,
            'meta' => $this->prioritizedMetadataModel->getMetaTagData(),
            'schema' => $this->getStructuredData($this->element)
        ];

        if ($render === false) {
            return $metadata;
        }

        // Output metadata
        if ($settings->enableMetadataRendering) {
            $output = $this->renderMetadata($metadata);
        }

        // Add metadata variable to Twig context
        if ($settings->toggleMetadataVariable && $context) {
            $context[$settings->metadataVariable] = $metadata;
        }

        return $output;
    }

    /**
     * @param      $element
     * @param null $site
     *
     * @return Metadata
     * @throws Exception
     * @throws InvalidConfigException
     * @throws Throwable
     */
    public function getPrioritizedMetadataModel($element, $site = null): Metadata
    {
        $elementMetadataAttributes = [];

        if ($element !== null) {
            $elementMetadataAttributes = SproutSeo::$app->elementMetadata->getRawMetadataFromElement($element);
        }

        $isPro = SproutBase::$app->settings->isEdition('sprout-seo', SproutSeo::EDITION_PRO);

        // Only allow Template Overrides if using Pro Edition
        if ($isPro && $this->templateMetadata && isset($this->templateMetadata['elementId'])) {
            /**
             * If an Element ID is provided as an Override, get our Metadata from the Element Metadata Field
             * associated with that Element ID This adds support for using Element Metadata fields on non URL-enabled
             * Elements such as Users and Tags
             *
             * Non URL-Enabled Elements don't resave metadata on their own. That will need to be done manually.
             */
            $elementOverride = Craft::$app->elements->getElementById($this->templateMetadata['elementId']);

            // Overwrite the Element Attributes if the template override Element ID returns an element
            if ($elementOverride) {
                $elementMetadataAttributes = SproutSeo::$app->elementMetadata->getRawMetadataFromElement($elementOverride);
            }

            // Merge our attributes overriding the Element attributes with Template overrides
            $attributes = array_filter(array_merge($elementMetadataAttributes, $this->templateMetadata));
        } else {
            $attributes = array_filter($elementMetadataAttributes);
        }

        return new Metadata($attributes);
    }

    public function getStructuredData($element = null): array
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
        if (isset($identity['address']) && $identity['address']) {
            $placeSchema = new WebsiteIdentityPlaceSchema();
            $placeSchema->addContext = true;

            $placeSchema->globals = $this->globals;
            $placeSchema->prioritizedMetadataModel = $this->prioritizedMetadataModel;

            if ($element !== null) {
                $placeSchema->element = $element;
            }

            $schema['place'] = $placeSchema;
        }

        if ($element !== null && isset($this->elementMetadataField) && $this->elementMetadataField->schemaTypeId) {
            $schema['mainEntity'] = $this->getMainEntityStructuredData($element);
        }

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
            $schemaUniqueKey = $this->prioritizedMetadataModel->getSchemaTypeId();
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
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function renderMetadata($metadata): string
    {
        $sproutSeoTemplatesPath = Craft::getAlias('@sproutseo/');

        Craft::$app->view->setTemplatesPath($sproutSeoTemplatesPath);

        $output = Craft::$app->view->renderTemplate('templates/_special/metadata', [
            'metadata' => $metadata
        ]);

        Craft::$app->view->setTemplatesPath(Craft::$app->path->getSiteTemplatesPath());

        return $output;
    }

    /**
     * Return a comma delimited string of robots meta settings
     *
     * @param array|string|null $robots
     *
     * @return string|null
     */
    public function prepareRobotsMetadataValue($robots = null)
    {
        if ($robots === null) {
            return null;
        }

        if (is_string($robots)) {
            return $robots;
        }

        $robotsMetaValue = '';

        foreach ($robots as $key => $value) {
            if ($value == '') {
                continue;
            }

            if ($robotsMetaValue == '') {
                $robotsMetaValue .= $key;
            } else {
                $robotsMetaValue .= ','.$key;
            }
        }

        return !empty($robotsMetaValue) ? $robotsMetaValue : null;
    }

    /**
     * Return an array of all robots settings set to their boolean value of on or off
     *
     * @param $robotsValues
     *
     * @return array
     */
    public function prepareRobotsMetadataForSettings($robotsValues): array
    {
        if (is_string($robotsValues)) {
            $robotsArray = explode(',', $robotsValues);

            $robotsSettings = [];

            foreach ($robotsArray as $key => $value) {
                $robotsSettings[$value] = 1;
            }
        } else {
            // Value from content table
            $robotsSettings = $robotsValues;
        }

        $robots = [
            'noindex' => 0,
            'nofollow' => 0,
            'noarchive' => 0,
            'noimageindex' => 0,
            'noodp' => 0,
            'noydir' => 0,
        ];

        foreach ($robots as $key => $value) {
            if (isset($robotsSettings[$key]) && $robotsSettings[$key]) {
                $robots[$key] = 1;
            }
        }

        return $robots;
    }

    /**
     * @param $image
     *
     * @return mixed
     */
    public function getImageId($image)
    {
        $imageId = $image;

        if (is_array($image)) {
            $imageId = $image[0];
        }

        return $imageId ?? null;
    }

    /**
     * Return pre-defined transform settings or the selected transform handle
     *
     * @param $transformHandle
     *
     * @return mixed
     */
    public function getSelectedTransform($transformHandle)
    {
        $defaultTransforms = [
            'sproutSeo-socialSquare' => [
                'mode' => 'crop',
                'width' => 400,
                'height' => 400,
                'quality' => 82,
                'position' => 'center-center'
            ],
            'sproutSeo-ogRectangle' => [
                'mode' => 'crop',
                'width' => 1200,
                'height' => 630,
                'quality' => 82,
                'position' => 'center-center'
            ],
            'sproutSeo-twitterRectangle' => [
                'mode' => 'crop',
                'width' => 1024,
                'height' => 512,
                'quality' => 82,
                'position' => 'center-center'
            ]
        ];

        return $defaultTransforms[$transformHandle] ?? ($transformHandle == '' ? null : $transformHandle);
    }
}
