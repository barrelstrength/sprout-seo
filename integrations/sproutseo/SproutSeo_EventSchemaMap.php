<?php
namespace Craft;

class SproutSeo_EventSchemaMap extends SproutSeoBaseSchemaMap
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
	public function getProperties()
	{
		return array();
	}
}