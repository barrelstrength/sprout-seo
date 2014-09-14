<?php
namespace Craft;

class SproutSeo_BasicMetaFieldModel extends BaseModel
{

	protected function defineAttributes()
	{
		return array(
			'title'       => AttributeType::String,
			'description' => AttributeType::String,
			'keywords'    => AttributeType::String,
			'author'      => array(AttributeType::String),
			'publisher'   => array(AttributeType::String),
		);
	}

}
