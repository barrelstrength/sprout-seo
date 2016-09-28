<?php
namespace Craft;

class SproutSeo_WebsiteIdentityPlaceSchemaMap extends SproutSeoBaseSchemaMap
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
	public function getProperties()
	{
		$schema['name'] = 'Place Schema Type';

		return array_filter($schema);
	}
}