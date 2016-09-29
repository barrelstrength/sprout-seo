<?php
namespace Craft;

class SproutSeo_CreativeWorkSchema extends SproutSeo_ThingSchema
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
	public function addProperties()
	{
		parent::addProperties();
	}
}