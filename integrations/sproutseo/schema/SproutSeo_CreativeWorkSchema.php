<?php
namespace Craft;

class SproutSeo_CreativeWorkSchema extends SproutSeo_ThingSchema
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
	 * @return array|null
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

		if ($elementType == 'Entry')
		{
			$this->addEntryElementProperties();
		}
	}

	public function addEntryElementProperties()
	{
		$identity = $this->globals['identity'];
		$element  = $this->element;

		if (isset($element->postDate))
		{
			$this->addDate('datePublished', $element->postDate);
		}

		if (method_exists($element, 'getAuthor'))
		{
			$person = new SproutSeo_PersonSchema();

			$person->globals                  = $this->globals;
			$person->element                  = $element->getAuthor();
			$person->prioritizedMetadataModel = $this->prioritizedMetadataModel;

			$this->addProperty('author', $person->getSchema());
			$this->addProperty('creator', $person->getSchema());
		}

		if ($identityType = $identity['@type'])
		{
			// Determine if we have an Organization or Person Schema Type
			$schemaModel = 'Craft\SproutSeo_WebsiteIdentity' . $identityType . 'Schema';

			$identitySchema = new $schemaModel();

			$identitySchema->globals                  = $this->globals;

			$this->addProperty('publisher', $identitySchema->getSchema());
		}
	}
}