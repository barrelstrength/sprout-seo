<?php
namespace Craft;

class SproutSeo_GeoSchema extends SproutSeoBaseSchema
{
	public $latitude;
	public $longitude;

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Geo';
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return 'GeoCoordinates';
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
		$this->addText('latitude', $this->latitude);
		$this->addText('longitude', $this->longitude);
	}
}