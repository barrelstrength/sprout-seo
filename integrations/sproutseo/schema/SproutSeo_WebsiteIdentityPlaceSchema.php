<?php
namespace Craft;

class SproutSeo_WebsiteIdentityPlaceSchema extends SproutSeoBaseSchema
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Place';
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return 'Place';
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
		$this->addUrl('url', $schema['url']);

		if (isset($schema['image'][0]))
		{
			$this->addImage('image', $schema['image'][0]);
		}

		$this->addTelephone('telephone', $schema['telephone']);

		if (isset($schema['addressId']) && $schema['addressId'])
		{
			$this->addAddress('address', $schema['addressId']);
		}

		if ((isset($schema['latitude']) && $schema['latitude']) && (isset($schema['longitude']) && $schema['longitude']))
		{
			$this->addGeo('geo', $schema['latitude'], $schema['longitude']);
		}

		if (count($socialProfiles))
		{
			$urls = sproutSeo()->elementMetadata->arrayColumn($socialProfiles, 'url');
			$this->addSameAs($urls);
		}
	}
}