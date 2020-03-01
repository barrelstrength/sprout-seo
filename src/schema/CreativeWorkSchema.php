<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\schema;


use barrelstrength\sproutseo\base\Schema;
use craft\elements\Entry;
use Exception;
use Throwable;

class CreativeWorkSchema extends ThingSchema
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Creative Work';
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return 'CreativeWork';
    }

    /**
     * @return bool
     */
    public function isUnlistedSchemaType(): bool
    {
        return false;
    }

    /**
     * @return array|null|void
     * @throws Exception
     * @throws Throwable
     */
    public function addProperties()
    {
        parent::addProperties();

        $this->removeProperty('name');

        $this->addText('headline', $this->prioritizedMetadataModel->getOptimizedTitle());
        $this->addText('keywords', $this->prioritizedMetadataModel->getOptimizedKeywords());
        $this->addDate('dateCreated', $this->element->dateCreated);
        $this->addDate('dateModified', $this->element->dateUpdated);

        if (get_class($this->element) === Entry::class) {
            $this->addEntryElementProperties();
        }
    }

    /**
     * @throws Exception
     */
    public function addEntryElementProperties()
    {
        $identity = $this->globals['identity'];
        $element = $this->element;

        $websiteIdentity = [
            'Person' => WebsiteIdentityPersonSchema::class,
            'Organization' => WebsiteIdentityOrganizationSchema::class
        ];

        if (isset($element->postDate)) {
            $this->addDate('datePublished', $element->postDate);
        }

        $identityType = $identity['@type'] ?? null;

        if (isset($websiteIdentity[$identityType])) {
            // Determine if we have an Organization or Person Schema Type
            $schemaModel = $websiteIdentity[$identityType];

            /**
             * @var Schema $identitySchema
             */
            $identitySchema = new $schemaModel();

            $identitySchema->globals = $this->globals;

            // Assume the Global Organization or Person is the Creator
            // More specific implementations will require a Custom Schema Integration
            $this->addProperty('author', $identitySchema->getSchema());
            $this->addProperty('creator', $identitySchema->getSchema());
            $this->addProperty('publisher', $identitySchema->getSchema());
        }
    }
}