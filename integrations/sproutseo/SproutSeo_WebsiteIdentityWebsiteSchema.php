<?php
namespace Craft;

class SproutSeo_WebsiteIdentityWebsiteSchema extends SproutSeoBaseSchema
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Website';
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return 'Website';
	}

	/**
	 * @return bool
	 */
	public function isUnlistedSchemaType()
	{
		return true;
	}

	/**
	 * @return array|null
	 */
	public function addProperties()
	{
		$schema         = $this->globals['identity'];
		$socialProfiles = $this->globals['social'];

		$this->addText('name', $schema['name']);
		$this->addText('alternateName', $schema['alternateName']);
		$this->addText('description', $schema['description']);
		$this->addText('about', $schema['description']);
		$this->addUrl('url', $schema['url']);
		$this->addImage('image', $schema['logo'][0]);

		if ($identityType = $schema['@type'])
		{
			// Determine if we have an Organization or Person Schema Type
			$schemaModel = 'Craft\SproutSeo_WebsiteIdentity' . $identityType . 'Schema';

			$identitySchema = new $schemaModel();
			$identitySchema->isContext = false;
			$identitySchema->globals = $this->globals;
			$identitySchema->element = $this->element;
			$identitySchema->prioritizedMetadataModel = $this->prioritizedMetadataModel;

			$this->addProperty('author', $identitySchema->getSchema());
		}

		$urls = array_column($socialProfiles, 'url');
		$this->addSameAs($urls);
	}
}