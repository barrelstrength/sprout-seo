<?php
namespace Craft;

class SproutSeo_WebsiteIdentityPersonSchema extends SproutSeoBaseSchema
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Person';
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return 'Person';
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
		$this->addTelephone('telephone', $schema['telephone']);
		$this->addEmail('email', $schema['email']);

		if (isset($schema['addressId']) && $schema['addressId'])
		{
			$this->addAddress('address', $schema['addressId']);
		}

		if ((isset($schema['latitude']) && $schema['latitude']) && (isset($schema['longitude']) && $schema['longitude']))
		{
			$this->addGeo('geo', $schema['latitude'], $schema['longitude']);
		}

		if (isset($schema['image'][0]))
		{
			$this->addImage('image', $schema['image'][0]);
		}

		$contacts = $this->globals['contacts'];
		$this->addContactPoints($contacts);

		$this->addText('gender', $schema['gender']);

		if (count($socialProfiles))
		{
			$urls = array_column($socialProfiles, 'url');
			$this->addSameAs($urls);
		}
	}
}