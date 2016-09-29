<?php
namespace Craft;

class SproutSeo_OrganizationSchema extends SproutSeoBaseSchema
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
	 * @return array|null
	 */
	public function addProperties()
	{
		parent::addProperties();
	}
}