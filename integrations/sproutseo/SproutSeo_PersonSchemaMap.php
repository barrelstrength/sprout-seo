<?php
namespace Craft;

class SproutSeo_PersonSchemaMap extends SproutSeoBaseSchemaMap
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
	 * @return array|null
	 */
	public function getProperties()
	{
		return array();
	}
}