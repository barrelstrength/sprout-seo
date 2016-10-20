<?php
namespace Craft;

class SproutSeo_AddressRecord extends BaseRecord
{
	/**
	 * Database table name
	 *
	 * @return string
	 */
	public function getTableName()
	{
		return 'sproutseo_address';
	}

	/**
	 * Database columns
	 *
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
			'modelId'            => array(AttributeType::Number),
			'countryCode'        => array(AttributeType::String),
			'administrativeArea' => array(AttributeType::String),
			'locality'           => array(AttributeType::String),
			'dependentLocality'  => array(AttributeType::String),
			'postalCode'         => array(AttributeType::String),
			'sortingCode'        => array(AttributeType::String),
			'address1'           => array(AttributeType::String),
			'address2'           => array(AttributeType::String)
		);
	}

	/**
	 * Create a new instance of the current class. This allows us to
	 * properly unit test our service layer.
	 *
	 * @return BaseRecord
	 */
	public function create()
	{
		$class  = get_class($this);
		$record = new $class();

		return $record;
	}
}
