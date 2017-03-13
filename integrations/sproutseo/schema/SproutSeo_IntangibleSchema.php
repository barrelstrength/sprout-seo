<?php
namespace Craft;

class SproutSeo_IntangibleSchema extends SproutSeo_ThingSchema
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Intangible';
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return 'Intangible';
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