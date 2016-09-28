<?php
namespace Craft;

class SproutSeo_WebsiteIdentityWebsiteSchemaMap extends SproutSeoBaseSchemaMap
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
	public function getProperties()
	{
		$schema['name'] = 'Website Schema Type';

		return array_filter($schema);
	}
}