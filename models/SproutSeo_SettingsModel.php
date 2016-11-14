<?php
namespace Craft;

class SproutSeo_SettingsModel extends BaseModel
{
	protected function defineAttributes()
	{
		return array(
			'pluginNameOverride'      => AttributeType::String,
			'appendTitleValue'        => AttributeType::Bool,
			'localeIdOverride'        => AttributeType::String,
			'enableCustomSections'    => AttributeType::Bool,
			'enableMetaDetailsFields' => AttributeType::Bool,
			'enableMetadataRendering' => array(AttributeType::Bool, 'default' => true),
			'metadataVariable'        => AttributeType::String
		);
	}

}
