<?php
namespace Craft;

class SproutSeo_SitemapModel extends BaseModel
{

	protected function defineAttributes()
	{
		return array(
			'id'              => array(AttributeType::Number),
			'elementGroupId'  => array(AttributeType::Number),
			'type'            => array(AttributeType::String),
			'url'             => array(AttributeType::String),
			'changeFrequency' => array(AttributeType::String, 'maxLength' => 3),
			'priority'        => array(AttributeType::String, 'maxLength' => 7),
			'enabled'         => array(AttributeType::Bool),
			'ping'            => array(AttributeType::Bool, 'default' => 0),
			'schemaMap'       => array(AttributeType::String),
			'dateUpdated'     => AttributeType::DateTime,
			'dateCreated'     => AttributeType::DateTime,
			'uid'             => AttributeType::String,
		);
	}

}
