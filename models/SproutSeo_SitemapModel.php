<?php
namespace Craft;

class SproutSeo_SitemapModel extends BaseModel
{

	protected function defineAttributes()
	{
		return array(
			'id'              => array(AttributeType::Number),
			'sectionId'       => array(AttributeType::Number),
			'url'             => array(AttributeType::String),
			'changeFrequency' => array(AttributeType::String,'maxLength' => 3),
			'priority'        => array(AttributeType::String,'maxLength' => 7),
			'enabled'         => array(AttributeType::Bool),
			'ping'            => array(AttributeType::Bool, 'default' => 0),
			'dateUpdated'     => AttributeType::DateTime,
			'dateCreated'     => AttributeType::DateTime,
			'uid'             => AttributeType::String,
		);
	}
	
}
