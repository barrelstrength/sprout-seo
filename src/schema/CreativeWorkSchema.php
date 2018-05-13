<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\schema;


class CreativeWorkSchema extends ThingSchema
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Creative Work';
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'CreativeWork';
    }

    /**
     * @return bool
     */
    public function isUnlistedSchemaType()
    {
        return false;
    }

    /**
     * @return array|null|void
     * @throws \Exception
     */
    public function addProperties()
    {
        parent::addProperties();

        $this->removeProperty('name');

        $this->addText('headline', $this->prioritizedMetadataModel->optimizedTitle);
        $this->addText('keywords', $this->prioritizedMetadataModel->optimizedKeywords);
        $this->addDate('dateCreated', $this->element->dateCreated);
        $this->addDate('dateModified', $this->element->dateUpdated);

        $elementType = $this->element->getElementType();

        if ($elementType == 'Entry') {
            $this->addEntryElementProperties();
        }
    }

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

        if (method_exists($element, 'getAuthor')) {
            $person = new WebsiteIdentityPersonSchema();

            $person->globals = $this->globals;
            $person->element = $element->getAuthor();
            $person->prioritizedMetadataModel = $this->prioritizedMetadataModel;

            $this->addProperty('author', $person->getSchema());
            $this->addProperty('creator', $person->getSchema());
        }

        $identityType = $identity['@type'];

        if (isset($websiteIdentity[$identityType])) {
            // Determine if we have an Organization or Person Schema Type
            $schemaModel = $websiteIdentity[$identityType];

            $identitySchema = new $schemaModel();

            $identitySchema->globals = $this->globals;

            $this->addProperty('publisher', $identitySchema->getSchema());
        }
    }
}