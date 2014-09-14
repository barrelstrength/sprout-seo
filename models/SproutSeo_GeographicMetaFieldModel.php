<?php
namespace Craft;

class SproutSeo_GeographicMetaFieldModel extends BaseModel
{

	protected function defineAttributes()
	{
		return array(
			'region'    => AttributeType::String,
			'placename' => AttributeType::String,
			'latitude'  => AttributeType::String,
			'longitude' => AttributeType::String
		);
	}

}
