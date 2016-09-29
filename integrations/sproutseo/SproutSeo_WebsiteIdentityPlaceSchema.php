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
		$schema['name'] = 'Place Schema Type';

		return array_filter($schema);
	}
}