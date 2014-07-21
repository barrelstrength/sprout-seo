<?php
namespace Craft;

class SproutSeo_RobotsMetaFieldModel extends BaseModel
{
	protected function defineAttributes()
	{
		return array(
			'canonical'   => AttributeType::String,
			'robots'      => AttributeType::String
		);
	}
}
