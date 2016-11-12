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
		$elementType = $this->element->getElementType();

		if ($elementType == 'User')
		{
			$this->addUserElementProperties();
		}
		else
		{
			parent::addProperties();
		}
	}

	public function addUserElementProperties()
	{
		$element = $this->element;

		$name = null;

		if (method_exists($element, 'getFullName'))
		{
			$name = $element->getFullName();
		}

		if (isset($element->firstName) && isset($element->lastName))
		{
			$this->addText('givenName', $element->firstName);
			$this->addText('familyName', $element->lastName);

			$name = $element->firstName . ' ' . $element->lastName;
		}

		$this->addText('name', $name);
		$this->addEmail('email', $element->email);
	}
}