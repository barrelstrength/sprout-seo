<?php
namespace Craft;

class SproutSeo_GlobalsRecord extends BaseRecord
{
	public function getTableName()
	{
		return 'sproutseo_globals';
	}

	protected function defineAttributes()
	{
		return array(
			'locale'    => array(AttributeType::Locale, 'required' => true),
			'identity'  => array(AttributeType::Mixed),
			'ownership' => array(AttributeType::Mixed),
			'contacts'  => array(AttributeType::Mixed),
			'social'    => array(AttributeType::Mixed),
		);
	}

	public function defineIndexes()
	{
		return array(
			array('columns' => array('id, locale'), 'unique' => true),
		);
	}
}