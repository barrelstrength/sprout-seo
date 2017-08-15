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
			'displayFieldHandles'     => AttributeType::Bool,
			'enableCustomSections'    => AttributeType::Bool,
			'enableMetaDetailsFields' => AttributeType::Bool,
			'enableMetadataRendering' => array(AttributeType::Bool, 'default' => true),
			'metadataVariable'        => AttributeType::String,
			'totalElementsPerSitemap' => AttributeType::Number,
			'enableDynamicSitemaps'   => AttributeType::Bool,
			'enable404RedirectLog'    => AttributeType::Bool
		);
	}

}
