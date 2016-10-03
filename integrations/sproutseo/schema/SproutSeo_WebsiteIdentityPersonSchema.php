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
		$person         = $this->globals['identity'];
		$socialProfiles = $this->globals['social'];

		$this->addText('name', $person['name']);
		$this->addText('alternateName', $person['alternateName']);
		$this->addText('description', $person['description']);
		$this->addUrl('url', $person['url']);
		$this->addTelephone('telephone', $person['telephone']);
		$this->addEmail('email', $person['email']);
		$this->addImage('image', $person['logo'][0]);

		$this->addText('gender', $person['gender']);

		if (count($socialProfiles))
		{
			$urls = array_column($socialProfiles, 'url');
			$this->addSameAs($urls);
		}
	}
}