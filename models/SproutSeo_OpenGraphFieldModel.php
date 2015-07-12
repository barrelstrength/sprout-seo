<?php
namespace Craft;

class SproutSeo_OpenGraphFieldModel extends BaseModel
{
	protected function defineAttributes()
	{
		return array(
			'ogTitle'        => array(AttributeType::String),
			'ogType'         => array(AttributeType::String),
			'ogUrl'          => array(AttributeType::String),

			'ogImage'        => array(AttributeType::Mixed),
			'ogImageSecure'  => array(AttributeType::Mixed),
			'ogImageWidth'   => array(AttributeType::Number),
			'ogImageHeight'  => array(AttributeType::Number),
			'ogImageType'    => array(AttributeType::String),

			'ogAuthor'       => array(AttributeType::String),
			'ogPublisher'    => array(AttributeType::String),

			'ogSiteName'     => array(AttributeType::String),
			'ogDescription'  => array(AttributeType::String),
			'ogAudio'        => array(AttributeType::String),
			'ogVideo'        => array(AttributeType::String),
			'ogLocale'       => array(AttributeType::String),
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
			'ogTitle'        => 'og:title',
			'ogType'         => 'og:type',
			'ogUrl'          => 'og:url',

			'ogImage'        => 'og:image',
			'ogImageSecure'  => 'og:image:secure_url',
			'ogImageWidth'   => 'og:image:width',
			'ogImageHeight'  => 'og:image:height',
			'ogImageType'    => 'og:image:type',

			'ogAuthor'       => 'og:author',
			'ogPublisher'    => 'og:publisher',

			'ogSiteName'     => 'og:site_name',
			'ogDescription'  => 'og:description',
			'ogAudio'        => 'og:audio',
			'ogVideo'        => 'og:video',
			'ogLocale'       => 'og:locale',
		);

		return $tagNames[$handle];
	}
}
