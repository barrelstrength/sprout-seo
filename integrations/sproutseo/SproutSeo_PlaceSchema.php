<?php
namespace Craft;

class SproutSeo_PlaceSchema extends SproutSeo_ThingSchema
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
	 * @return array|null
	 */
	public function addProperties()
	{
		parent::addProperties();
	}
}