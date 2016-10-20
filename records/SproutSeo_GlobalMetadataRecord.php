<?php
namespace Craft;

class SproutSeo_GlobalMetadataRecord extends BaseRecord
{
	/**
	 * @return string
	 */
	public function getTableName()
	{
		return 'sproutseo_metadata_globals';
	}

	/**
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
			'locale'    => array(AttributeType::Locale, 'required' => true),
			'meta'      => array(AttributeType::Mixed),
			'identity'  => array(AttributeType::Mixed),
			'ownership' => array(AttributeType::Mixed),
			'contacts'  => array(AttributeType::Mixed),
			'social'    => array(AttributeType::Mixed),
			'robots'    => array(AttributeType::Mixed),
			'addressId' => array(AttributeType::Number),
			'settings'  => array(AttributeType::Mixed),
		);
	}

	/**
	 * @return array
	 */
	public function defineIndexes()
	{
		return array(
			array('columns' => array('id, locale'), 'unique' => true),
		);
	}
}