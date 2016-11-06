<?php
namespace Craft;

class SproutSeo_PersonSchema extends SproutSeo_ThingSchema
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
		return false;
	}

	/**
	 * @return array|null
	 */
	public function addProperties()
	{
		parent::addProperties();

		$element = $this->element;

		if ($element->firstName && $element->lastName)
		{
			$this->addText('givenName', $element->firstName);
			$this->addText('familyName', $element->lastName);
		}

		$this->addEmail('email', $element->email);
	}
}