<?php
namespace Craft;

class SproutSeo_BasicMetaFieldModel extends BaseModel
{

	protected function defineAttributes()
	{
		return array(
			'title'       => AttributeType::String,
			'description' => AttributeType::String,
			'keywords'    => AttributeType::String,
			'author'      => array(AttributeType::String),
			'publisher'   => array(AttributeType::String),

			'locale'      => array(AttributeType::Locale, 'required' => true),
		);
	}

	public function getMetaTagData(SproutSeo_MetaModel $meta)
	{
		$tagData = array();

		foreach ($this->getAttributes() as $key => $value)
		{
			if ($meta->{$key})
			{
				$value = craft()->config->parseEnvironmentString($meta->{$key});
				$tagData[$key] = $value;
			}
		}

		return $tagData;
	}
}
