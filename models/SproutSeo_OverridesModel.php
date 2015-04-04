<?php
namespace Craft;

class SproutSeo_OverridesModel extends BaseModel
{
	protected function defineAttributes()
	{
		return array(
			'id'             => array(AttributeType::Number),
			'entryId'        => array(AttributeType::Number),
			'title'          => array(AttributeType::String),
			'description'    => array(AttributeType::String),
			'keywords'       => array(AttributeType::String),
			'author'         => array(AttributeType::String),
			'publisher'      => array(AttributeType::String),

			'robots'         => array(AttributeType::String),
			'canonical'      => array(AttributeType::String),

			'region'         => array(AttributeType::String),
			'placename'      => array(AttributeType::String),
			'position'       => array(AttributeType::String),
			'latitude'       => array(AttributeType::String),
			'longitude'      => array(AttributeType::String),

			'ogTitle'        => array(AttributeType::String),
			'ogType'         => array(AttributeType::String),
			'ogUrl'          => array(AttributeType::String),
			'ogImage'        => array(AttributeType::String),
			'ogImageSecure'  => array(AttributeType::String),
			'ogImageWidth'   => array(AttributeType::String),
			'ogImageHeight'  => array(AttributeType::String),
			'ogImageType'    => array(AttributeType::String),
			'ogSiteName'     => array(AttributeType::String),
			'ogDescription'  => array(AttributeType::String),
			'ogAudio'        => array(AttributeType::String),
			'ogVideo'        => array(AttributeType::String),
			'ogLocale'       => array(AttributeType::String),

			// Store the Twitter Card Type and global fields
			'twitterCard'        => array(AttributeType::String),
			'twitterSite'        => array(AttributeType::String),
			'twitterTitle'       => array(AttributeType::String),
			'twitterCreator'     => array(AttributeType::String),
			'twitterDescription' => array(AttributeType::String),

			'twitterUrl'   => array(AttributeType::String),
			'twitterImage' => array(AttributeType::String),

			// Fields for Twitter Player Card
			'twitterPlayer'                  => array(AttributeType::String),
			'twitterPlayerStream'            => array(AttributeType::String),
			'twitterPlayerStreamContentType' => array(AttributeType::String),
			'twitterPlayerWidth'             => array(AttributeType::String),
			'twitterPlayerHeight'            => array(AttributeType::String),
		);
	}
}
