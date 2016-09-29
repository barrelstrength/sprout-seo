<?php
namespace Craft;

class SproutSeo_PersonSchema extends SproutSeoBaseSchema
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
	public function addProperties()
	{
		parent::addProperties();
	}
}