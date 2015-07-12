<?php
namespace Craft;

class SproutSeo_TwitterCardFieldModel extends BaseModel
{
	protected function defineAttributes()
	{
		return array(
			'twitterCard'                    => AttributeType::String,
			
			// Fields for all twitter cards
			'twitterSite'                    => AttributeType::String,
			'twitterTitle'                   => AttributeType::String,
			'twitterCreator'                 => AttributeType::String,
			'twitterDescription'             => AttributeType::String,
			
			'twitterUrl'                     => array(AttributeType::String),
			'twitterImage'                   => array(AttributeType::String),
			
			// Fields for Player Card
			'twitterPlayer'                  => AttributeType::String,
			'twitterPlayerStream'            => AttributeType::String,
			'twitterPlayerStreamContentType' => AttributeType::String,
			'twitterPlayerWidth'             => AttributeType::String,
			'twitterPlayerHeight'            => AttributeType::String,
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
				$tagData[$this->getMetaTagName($key)] = $value;
			}
		}

		return $tagData;
	}

	public function getMetaTagName($handle)
	{
		$tagNames = array(
			'twitterCard'        => 'twitter:card',

			'twitterSite'        => 'twitter:site',
			'twitterCreator'     => 'twitter:creator',
			'twitterTitle'       => 'twitter:title',
			'twitterDescription' => 'twitter:description',

			'twitterUrl'     => 'twitter:url',
			'twitterImage'   => 'twitter:image',

			// Fields for Twitter Player Card
			'twitterPlayer'                  => 'twitter:player',
			'twitterPlayerStream'            => 'twitter:player:stream',
			'twitterPlayerStreamContentType' => 'twitter:player:stream:content_type',
			'twitterPlayerWidth'             => 'twitter:player:width',
			'twitterPlayerHeight'            => 'twitter:player:height',
		);

		return $tagNames[$handle];
	}
}
