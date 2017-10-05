<?php
namespace Craft;

class SproutSeo_ContactPointSchema extends SproutSeoBaseSchema
{
	public $contact;

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Contact Point';
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return 'ContactPoint';
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
		$contact = $this->contact;

		if (!$contact)
		{
			return null;
		}

		$this->addText('contactType', $contact['contactType']);
		$this->addTelephone('telephone', $contact['telephone']);
	}
}