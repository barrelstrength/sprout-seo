<?php
namespace Craft;

class SproutSeo_TwitterCardFieldModel extends BaseModel
{
	protected function defineAttributes()
	{
		return array(
			'twitterCard'                         => AttributeType::String,
			// fields for all twitter cards
			'twitterSite'                         => AttributeType::String,
			'twitterTitle'                        => AttributeType::String,
			'twitterCreator'                      => AttributeType::String,
			'twitterDescription'                  => AttributeType::String,
			// fields for Summary Card
			'twitterSummaryImageSource'           => AttributeType::String,
			// Fields for Summary Large Image Card
			'twitterSummaryLargeImageImageSource' => AttributeType::String,
			// Fields for Photo Card
			'twitterPhotoImageSource'             => AttributeType::String,
			// Fields for Player Card
			'twitterPlayerImageSource'            => AttributeType::String,
			'twitterPlayer'                       => AttributeType::String,
			'twitterPlayerStream'                 => AttributeType::String,
			'twitterPlayerStreamContentType'      => AttributeType::String,
			'twitterPlayerWidth'                  => AttributeType::String,
			'twitterPlayerHeight'                 => AttributeType::String,
		);
	}
}
