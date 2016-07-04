<?php
namespace Craft;

class SproutSeo_SettingsModel extends BaseModel
{

	protected function defineAttributes()
	{
		return array(
			'pluginNameOverride' => AttributeType::String,
			'appendSiteName'     => AttributeType::Bool,
			'seoDivider'         => AttributeType::String,
			'sitemapTemplate'    => AttributeType::String,
			'ogType'             => AttributeType::String,
			'twitterCard'        => AttributeType::String,
			'templateFolder'     => AttributeType::String,
			'pingServices'       => AttributeType::Mixed,
		);
	}

}
