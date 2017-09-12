<?php
namespace Craft;

/**
 * Class SproutSeo_MetadataSitemapModel
 *
 * This class is used to manage the ajax updates of the sitemap settings on the
 * sitemap tab. The attributes are a subset of the SproutSeo_MetadataModel
 */
class SproutSeo_MetadataSitemapModel extends BaseModel
{
	protected function defineAttributes()
	{
		return array(
			'id'                  => array(AttributeType::Number),
			'name'                => array(AttributeType::String),
			'handle'              => array(AttributeType::String),
			'urlEnabledSectionId' => array(AttributeType::Number),
			'type'                => array(AttributeType::String),
			'uri'                 => array(AttributeType::String),
			'changeFrequency'     => array(AttributeType::String, 'maxLength' => 3),
			'priority'            => array(AttributeType::String, 'maxLength' => 7),
			'enabled'             => array(AttributeType::Bool),
		);
	}
}
