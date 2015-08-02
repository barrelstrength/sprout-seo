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

	public function getMetaTagData(SproutSeo_MetaModel $meta)
	{
		$tagData = array();

		foreach ($this->getAttributes() as $key => $value)
		{
			if ($meta->{$key})
			{
				$value = $meta->{$key};

				if ($key == 'robots')
				{
					$value = $meta->robots;
				}

				$tagData[$key] = $value;
			}
		}

		return $tagData;
	}

	protected function determineRobotsOutput($robotsArray)
	{
		$robotsMap = array(
			"noindex"      => "index",
			"nofollow"     => "follow",
			"noarchive"    => "archive",
			"noimageindex" => "imageindex",
			"nosnippet"    => "snippet",
			"noodp"        => "odp",
			"noydir"       => "ydir"
		);

		$robotOutputValues = "";

		foreach ($robotsMap as $negativeValue => $positiveValue)
		{
			$robotString = StringHelper::arrayToString($robotsArray);

			if (stristr($robotString, $negativeValue) === FALSE)
			{
				$robotOutputValues .= $robotsMap[$negativeValue] . ",";
			}
			else
			{
				$robotOutputValues .= $negativeValue . ",";
			}
		}

		// Remove the trailing comma from our string
		$robotOutputValues = rtrim($robotOutputValues, ",");

		return $robotOutputValues;
	}
}
