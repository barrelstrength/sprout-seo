<?php
namespace Craft;

class SproutSeo_GeographicMetaFieldModel extends BaseModel
{

	protected function defineAttributes()
	{
		return array(
			'region'    => AttributeType::String,
			'placename' => AttributeType::String,
			'position'  => AttributeType::String,
			'latitude'  => AttributeType::String,
			'longitude' => AttributeType::String
		);
	}

	public function getMetaTagData(SproutSeo_MetaModel $meta)
	{
		$tagData = array();

		foreach ($this->getAttributes() as $key => $value)
		{
			if ($key == 'latitude' or $key == 'longitude')
			{
				break;
			}

			if ($meta->{$key})
			{
				$value = $meta[$key];

				if ($key == 'position')
				{
					$value = SproutSeoMetaHelper::prepareGeoPosition($meta);
				}

				$tagData[$this->getMetaTagName($key)] = $value;
			}
		}

		return $tagData;
	}

	public function getMetaTagName($handle)
	{
		$tagNames = array(
			'region'    => 'geo.region',
			'placename' => 'geo.placename',
			'position'  => 'geo.position'
		);

		return $tagNames[$handle];
	}
}
