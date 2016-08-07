<?php
namespace Craft;

class SproutSeo_ContactPointSchemaMap extends BaseSproutSeoSchemaMap
{
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
	 * @return array|null
	 */
	public function getAttributes()
	{
		$contact = $this->attributes['contact'];

		if (!$contact)
		{
			return null;
		}

		$schema['contactType'] = isset($contact['contactType']) ? $contact['contactType'] : null;
		$schema['telephone']   = isset($contact['telephone']) ? $contact['telephone'] : null;

		return array_filter($schema);
	}
}