<?php
namespace Craft;

class SproutSeo_OrganizationSchema extends SproutSeo_ThingSchema
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Organization';
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return 'Organization';
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
	}
}