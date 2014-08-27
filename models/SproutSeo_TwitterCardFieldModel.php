<?php
namespace Craft;

class SproutSeo_TwitterCardFieldModel extends BaseModel
{
	protected function defineAttributes()
	{
		return array(
			'twitterCard'                    => AttributeType::String,
			
			// Fields for all twitter cards
			'twitterSite'                    => AttributeType::String,
			'twitterTitle'                   => AttributeType::String,
			'twitterCreator'                 => AttributeType::String,
			'twitterDescription'             => AttributeType::String,
			
			'twitterUrl'                     => array(AttributeType::String),
			'twitterImage'                   => array(AttributeType::String),
			
			// Fields for Player Card
			'twitterPlayer'                  => AttributeType::String,
			'twitterPlayerStream'            => AttributeType::String,
			'twitterPlayerStreamContentType' => AttributeType::String,
			'twitterPlayerWidth'             => AttributeType::String,
			'twitterPlayerHeight'            => AttributeType::String,
		);
	}
}
