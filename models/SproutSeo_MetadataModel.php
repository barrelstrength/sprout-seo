<?php
namespace Craft;

class SproutSeo_MetadataModel extends BaseModel
{
	protected $basicMeta = array();
	protected $robotsMeta = array();
	protected $geographicMeta = array();
	protected $openGraphMeta = array();
	protected $twitterCardsMeta = array();

	protected function defineAttributes()
	{
		$sitemap = array(
			'elementGroupId' => array(AttributeType::Number),
			'type'           => array(AttributeType::String),

			'sitemapUrl'             => array(AttributeType::String),
			'sitemapPriority'        => array(
				AttributeType::Number,
				'maxLength' => 2,
				'decimals'  => 1,
				'default'   => '0.5',
				'required'  => true
			),
			'sitemapChangeFrequency' => array(
				AttributeType::String,
				'maxLength' => 7,
				'default'   => 'weekly',
				'required'  => true
			),

			'enabled'             => array(
				AttributeType::Bool,
				'default'  => false,
				'required' => true
			),
			'isSitemapCustomPage' => array(
				AttributeType::Bool,
				'default'  => false,
				'required' => true
			),
			'schemaMap'           => array(AttributeType::String),

			'locale'      => array(AttributeType::String),
			'dateUpdated' => array(AttributeType::DateTime),
			'dateCreated' => array(AttributeType::DateTime),
			'uid'         => array(AttributeType::String),
		);

		// @todo - do we need all these values here? Some could just be assigned elsewhere:
		// name => title, url => canonical, default not in use...
		$metaTags = array(
			'id'                    => array(AttributeType::Number),
			'elementId'               => array(AttributeType::Number),
			'default'               => array(AttributeType::String),
			'name'                  => array(AttributeType::String),
			'handle'                => array(AttributeType::String),
			'appendTitleValue'      => array(AttributeType::String, 'default' => null),
			'url'                   => array(AttributeType::String),
			'optimizedTitle'        => array(AttributeType::String),
			'optimizedDescription'  => array(AttributeType::String),
			'optimizedImage'        => array(AttributeType::String),
			'optimizedKeywords'     => array(AttributeType::String),
			'customizationSettings' => array(AttributeType::String),
		);

		$this->basicMeta = array(
			'title'       => array(AttributeType::String),
			'description' => array(AttributeType::String),
			'keywords'    => array(AttributeType::String),
			'author'      => array(AttributeType::String),
			'publisher'   => array(AttributeType::String),
		);

		$this->robotsMeta = array(
			'robots'    => array(AttributeType::String),
			'canonical' => array(AttributeType::String),
		);

		$this->geographicMeta = array(
			'region'    => array(AttributeType::String),
			'placename' => array(AttributeType::String),
			'position'  => array(AttributeType::String),
			'latitude'  => array(AttributeType::String),
			'longitude' => array(AttributeType::String),
		);

		$this->openGraphMeta = array(
			'ogType'        => array(AttributeType::String),
			'ogSiteName'    => array(AttributeType::String),
			'ogAuthor'      => array(AttributeType::String),
			'ogPublisher'   => array(AttributeType::String),
			'ogUrl'         => array(AttributeType::String),
			'ogTitle'       => array(AttributeType::String),
			'ogDescription' => array(AttributeType::String),
			'ogImage'       => array(AttributeType::String),
			'ogImageSecure' => array(AttributeType::String),
			'ogImageWidth'  => array(AttributeType::String),
			'ogImageHeight' => array(AttributeType::String),
			'ogImageType'   => array(AttributeType::String),
			'ogAudio'       => array(AttributeType::String),
			'ogVideo'       => array(AttributeType::String),
			'ogLocale'      => array(AttributeType::String),
		);

		$this->twitterCardsMeta = array(
			'twitterCard'                    => array(AttributeType::String),
			'twitterSite'                    => array(AttributeType::String),
			'twitterCreator'                 => array(AttributeType::String),
			'twitterUrl'                     => array(AttributeType::String),
			'twitterTitle'                   => array(AttributeType::String),
			'twitterDescription'             => array(AttributeType::String),
			'twitterImage'                   => array(AttributeType::String),
			'twitterPlayer'                  => array(AttributeType::String),
			'twitterPlayerStream'            => array(AttributeType::String),
			'twitterPlayerStreamContentType' => array(AttributeType::String),
			'twitterPlayerWidth'             => array(AttributeType::String),
			'twitterPlayerHeight'            => array(AttributeType::String),
		);

		$attributes = array_merge(
			$sitemap,
			$metaTags,
			$this->basicMeta,
			$this->robotsMeta,
			$this->geographicMeta,
			$this->openGraphMeta,
			$this->twitterCardsMeta
		);

		return $attributes;
	}

	public function getMetaTagData()
	{
		$metaTagData = array();

		$metaTagData['basic']     = $this->getBasicMetaTagData();
		$metaTagData['robots']    = $this->getRobotsMetaTagData();
		$metaTagData['geo']       = $this->getGeographicMetaTagData();
		$metaTagData['openGraph'] = $this->getOpenGraphMetaTagData();
		$metaTagData['twitter']   = $this->getTwitterCardMetaTagData();

		return $metaTagData;
	}

	/**
	 * @param string $type
	 * @param array  $overrideInfo
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function setMeta($type = 'global', $overrideInfo = array())
	{
		switch ($type)
		{
			case SproutSeo_MetaTagLevels::Code:
				$this->setAttributes($this->getCodeOverride($overrideInfo));
				break;

			case SproutSeo_MetaTagLevels::Entry:
				$this->setAttributes($this->getEntryOverride($overrideInfo));
				break;

			case SproutSeo_MetaTagLevels::MetadataGroup:
				$this->setAttributes($this->getMetadataGroup($overrideInfo));
				break;

			case SproutSeo_MetaTagLevels::GlobalFallback:
				$globals                = sproutSeo()->schema->getGlobals();
				$globalFallbackMetaTags = $globals->meta;

				$this->setAttributes($globalFallbackMetaTags);
				break;
		}

		SproutSeoOptimizeHelper::prepareAssetUrls($this);

		return $this;
	}

	/**
	 * Get Meta Tag Content based on an Element ID
	 *
	 * @param $overrideInfo
	 *
	 * @return array
	 */
	protected function getEntryOverride($overrideInfo)
	{
		if (isset($overrideInfo['elementId']))
		{
			// @todo - revisit when adding internationalization
			$locale        = (defined('CRAFT_LOCALE') ? CRAFT_LOCALE : craft()->locale->getId());
			$entryOverride = sproutSeo()->metadata->getMetadataContentByElementId($overrideInfo['elementId'], $locale);

			return $entryOverride->getAttributes();
		}

		return array();
	}

	/**
	 * Process any Meta Tags provided in via the templates and create a SproutSeo_MetaTagsModel
	 *
	 * @param $overrideInfo
	 *
	 * @return SproutSeo_MetadataModel
	 */
	protected function getCodeOverride($overrideInfo)
	{
		if (!empty($overrideInfo))
		{
			return $overrideInfo;
		}

		return array();
	}

	/**
	 * Create our default Metadata Group SproutSeo_MetaTagsModel
	 *
	 * @param $overrideInfo
	 *
	 * @return SproutSeo_MetadataModel
	 */
	protected function getMetadataGroup($overrideInfo)
	{
		$attributes = array();

		if ($overrideInfo)
		{
			$elementGroupId = $overrideInfo['elementGroupId'];
			$elementTable   = $overrideInfo['elementTable'];

			$metaTagsModel = sproutSeo()->metadata->getMetadataGroupByInfo($elementTable, $elementGroupId);
			$attributes    = $metaTagsModel->getAttributes();
		}

		return $attributes;
	}

	protected function getBasicMetaTagData()
	{
		$tagData = array();

		foreach ($this->basicMeta as $key => $value)
		{
			if ($this->{$key})
			{
				$value         = craft()->config->parseEnvironmentString($this->{$key});
				$tagData[$key] = $value;
			}
		}

		return $tagData;
	}

	protected function getRobotsMetaTagData()
	{
		$tagData = array();

		foreach ($this->robotsMeta as $key => $value)
		{
			if ($this->{$key})
			{
				$value = $this->{$key};

				if ($key == 'robots')
				{
					$value = $this->robots;
				}

				$tagData[$key] = $value;
			}
		}

		return $tagData;
	}

	protected function getGeographicMetaTagData()
	{
		$tagData = array();

		foreach ($this->geographicMeta as $key => $value)
		{
			if ($key == 'latitude' or $key == 'longitude')
			{
				break;
			}

			if ($this->{$key})
			{
				$value = $this[$key];

				if ($key == 'position')
				{
					$value = SproutSeoOptimizeHelper::prepareGeoPosition($this);
				}

				$tagData[$this->getMetaTagName($key)] = $value;
			}
		}

		return $tagData;
	}

	protected function getOpenGraphMetaTagData()
	{
		$tagData = array();

		foreach ($this->openGraphMeta as $key => $value)
		{
			if ($this->{$key})
			{
				$value                                = craft()->config->parseEnvironmentString($this->{$key});
				$tagData[$this->getMetaTagName($key)] = $value;
			}
		}

		return $tagData;
	}

	protected function getTwitterCardMetaTagData()
	{
		$tagData = array();

		foreach ($this->twitterCardsMeta as $key => $value)
		{
			if ($this->{$key})
			{
				$value                                = craft()->config->parseEnvironmentString($this->{$key});
				$tagData[$this->getMetaTagName($key)] = $value;
			}
		}

		return $tagData;
	}

	protected function getMetaTagName($handle)
	{
		// Map tag names to their handles
		$tagNames = array(

			// Geographic
			'region'                         => 'geo.region',
			'placename'                      => 'geo.placename',
			'position'                       => 'geo.position',

			// Open Graph
			'ogType'                         => 'og:type',
			'ogSiteName'                     => 'og:site_name',
			'ogAuthor'                       => 'og:author',
			'ogPublisher'                    => 'og:publisher',
			'ogUrl'                          => 'og:url',
			'ogTitle'                        => 'og:title',
			'ogDescription'                  => 'og:description',
			'ogImage'                        => 'og:image',
			'ogImageSecure'                  => 'og:image:secure_url',
			'ogImageWidth'                   => 'og:image:width',
			'ogImageHeight'                  => 'og:image:height',
			'ogImageType'                    => 'og:image:type',
			'ogAudio'                        => 'og:audio',
			'ogVideo'                        => 'og:video',
			'ogLocale'                       => 'og:locale',

			// Twitter Cards
			'twitterCard'                    => 'twitter:card',
			'twitterSite'                    => 'twitter:site',
			'twitterCreator'                 => 'twitter:creator',
			'twitterTitle'                   => 'twitter:title',
			'twitterDescription'             => 'twitter:description',
			'twitterUrl'                     => 'twitter:url',
			'twitterImage'                   => 'twitter:image',
			'twitterPlayer'                  => 'twitter:player',
			'twitterPlayerStream'            => 'twitter:player:stream',
			'twitterPlayerStreamContentType' => 'twitter:player:stream:content_type',
			'twitterPlayerWidth'             => 'twitter:player:width',
			'twitterPlayerHeight'            => 'twitter:player:height',
		);

		return $tagNames[$handle];
	}

	/**
	 * Returns
	 *
	 * @return array
	 **/
	public function getCustomizationSettings()
	{
		$response = array(
			'basicMetaMetadataGroupEnabled'   => 0,
			'openGraphMetadataGroupEnabled'   => 0,
			'twitterCardMetadataGroupEnabled' => 0,
			'geoMetadataGroupEnabled'         => 0,
			'robotsMetadataGroupEnabled'      => 0
		);

		if ($this->customizationSettings)
		{
			$response = json_decode($this->customizationSettings, true);
		}

		return $response;
	}
}