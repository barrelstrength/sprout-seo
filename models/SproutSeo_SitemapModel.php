<?php
namespace Craft;

class SproutSeo_SitemapModel extends BaseModel
{

	protected function defineAttributes()
	{
		return array(
			'id'                     => array(AttributeType::Number),
			'name'                   => array(AttributeType::String),
			'handle'                 => array(AttributeType::String),
			'elementGroupId'         => array(AttributeType::Number),
			'type'                   => array(AttributeType::String),
			'sitemapUrl'             => array(AttributeType::String),
			'sitemapChangeFrequency' => array(AttributeType::String, 'maxLength' => 3),
			'sitemapPriority'        => array(AttributeType::String, 'maxLength' => 7),
			'isSitemapCustomPage'    => array(AttributeType::Bool),
			'enabled'                => array(AttributeType::Bool),
			'dateUpdated'            => AttributeType::DateTime,
			'dateCreated'            => AttributeType::DateTime,
			'uid'                    => AttributeType::String,
		);
	}

}
