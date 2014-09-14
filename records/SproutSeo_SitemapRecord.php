<?php
namespace Craft;

class SproutSeo_SitemapRecord extends BaseRecord
{
	public function getTableName()
	{
		return 'sproutseo_sitemap';
	}

	public function defineAttributes()
	{
		return array(
			'sectionId' => array(AttributeType::Number),
			'url'       => array(AttributeType::String),
			'priority'  => array(
				AttributeType::Number,
				'maxLength' => 2,
				'decimals' => 1,
				'default' => '0.5',
				'required' => true
			),
			'changeFrequency' => array(
				AttributeType::String,
				'maxLength' => 7,
				'default' => 'weekly',
				'required' => true
			),
			'enabled' => array(
				AttributeType::Bool,
				'default' => false,
				'required' => true
			),
			'ping' => array(
				AttributeType::Bool,
				'default' => false,
				'required' => true
			),
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
		$class = get_class($this);
		
		$record = new $class();

		return $record;
	}
}
