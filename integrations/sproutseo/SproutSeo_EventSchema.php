<?php
namespace Craft;

class SproutSeo_EventSchema extends SproutSeo_ThingSchema
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Event';
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return 'Event';
	}

	/**
	 * @return array|null
	 */
	public function addProperties()
	{
		parent::addProperties();
	}
}