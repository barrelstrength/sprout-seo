<?php
namespace Craft;

class SproutSeo_MainEntityOfPageSchema extends SproutSeoBaseSchema
{
	/**
	 * @var
	 */
	public $id;

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Main Entity Of Page';
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return 'WebPage';
	}

	/**
	 * @return bool
	 */
	public function isUnlistedSchemaType()
	{
		return true;
	}

	/**
	 * @return array|null
	 */
	public function addProperties()
	{
		$this->addProperty('@id', $this->id);
	}
}