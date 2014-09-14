<?php
namespace Craft;

class SproutSeo_OpenGraphFieldModel extends BaseModel
{
	protected function defineAttributes()
	{
		return array(
			'ogTitle'        => array(AttributeType::String),
			'ogType'         => array(AttributeType::String),
			'ogUrl'          => array(AttributeType::String),
			'ogImage'        => array(AttributeType::Number),
			'ogAuthor'       => array(AttributeType::String),
			'ogPublisher'    => array(AttributeType::String),
			'ogSiteName'     => array(AttributeType::String),
			'ogDescription'  => array(AttributeType::String),
			'ogAudio'        => array(AttributeType::String),
			'ogVideo'        => array(AttributeType::String),
			'ogLocale'       => array(AttributeType::String),
		);
	}
}
