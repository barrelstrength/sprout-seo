<?php
namespace Craft;

class SproutSeo_CreativeWorkSchemaMap extends SproutSeoBaseSchemaMap
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Creative Work';
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return 'CreativeWork';
	}

	/**
	 * @return array|null
	 */
	public function getProperties()
	{
		return array();
	}
}