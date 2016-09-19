<?php
namespace Craft;

class SproutSeo_SettingsModel extends BaseModel
{

	protected function defineAttributes()
	{
		return array(
			'pluginNameOverride'    => AttributeType::String,
			'appendTitleValue'      => AttributeType::Bool,
			'seoDivider'            => AttributeType::String,
			'localeIdOverride'      => AttributeType::String,
			'enableCodeOverrides'   => AttributeType::Bool,
			'advancedCustomization' => AttributeType::Bool,
			'templateFolder'        => AttributeType::String,
		);
	}

}
