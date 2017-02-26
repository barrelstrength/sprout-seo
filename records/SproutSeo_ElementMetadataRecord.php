<?php
namespace Craft;

class SproutSeo_ElementMetadataRecord extends BaseRecord
{
	/**
	 * @return string
	 */
	public function getTableName()
	{
		return 'sproutseo_metadata_elements';
	}

	/**
	 * @return array
	 */
	public function defineAttributes()
	{
		return array(
			'elementId'            => array(AttributeType::Number, 'required' => true),
			'locale'               => array(AttributeType::Locale, 'required' => true),

			// Optimized Meta
			'optimizedTitle'       => array(AttributeType::String, 'required' => false),
			'optimizedDescription' => array(AttributeType::String, 'required' => false),
			'optimizedImage'       => array(AttributeType::String, 'required' => false),
			'optimizedKeywords'    => array(AttributeType::String, 'required' => false),

			// Structured Data
			'schemaTypeId'         => array(AttributeType::String),
			'schemaOverrideTypeId' => array(AttributeType::String),

			'enableMetaDetailsSearch'      => array(AttributeType::Bool, 'default' => false, 'required' => false),
			'enableMetaDetailsOpenGraph'   => array(AttributeType::Bool, 'default' => false, 'required' => false),
			'enableMetaDetailsTwitterCard' => array(AttributeType::Bool, 'default' => false, 'required' => false),
			'enableMetaDetailsGeo'         => array(AttributeType::Bool, 'default' => false, 'required' => false),
			'enableMetaDetailsRobots'      => array(AttributeType::Bool, 'default' => false, 'required' => false),
			'title'                        => array(AttributeType::String),
			'description'                  => array(AttributeType::String),
			'keywords'                     => array(AttributeType::String),

			'robots'    => array(AttributeType::String),
			'canonical' => array(AttributeType::String),

			'region'    => array(AttributeType::String),
			'placename' => array(AttributeType::String),
			'latitude'  => array(AttributeType::String),
			'longitude' => array(AttributeType::String),

			'ogType'        => array(AttributeType::String),
			'ogUrl'         => array(AttributeType::String),
			'ogSiteName'    => array(AttributeType::String),
			'ogTitle'       => array(AttributeType::String),
			'ogDescription' => array(AttributeType::String),
			'ogImage'       => array(AttributeType::String),
			'ogTransform'   => array(AttributeType::String),
			'ogAuthor'      => array(AttributeType::String),
			'ogPublisher'   => array(AttributeType::String),
			'ogAudio'       => array(AttributeType::String),
			'ogVideo'       => array(AttributeType::String),
			'ogLocale'      => array(AttributeType::String),

			'twitterCard'                    => array(AttributeType::String),
			'twitterUrl'                     => array(AttributeType::String),
			'twitterSite'                    => array(AttributeType::String),
			'twitterTitle'                   => array(AttributeType::String),
			'twitterDescription'             => array(AttributeType::String),
			'twitterImage'                   => array(AttributeType::String),
			'twitterTransform'               => array(AttributeType::String),
			'twitterCreator'                 => array(AttributeType::String),

			// Fields for Twitter Player Card
			'twitterPlayer'                  => array(AttributeType::String),
			'twitterPlayerStream'            => array(AttributeType::String),
			'twitterPlayerStreamContentType' => array(AttributeType::String),
			'twitterPlayerWidth'             => array(AttributeType::String),
			'twitterPlayerHeight'            => array(AttributeType::String),
		);
	}

	/**
	 * @return array
	 */
	public function defineIndexes()
	{
		return array(
			array('columns' => array('elementId, locale'), 'unique' => true),
		);
	}

	public function defineRelations()
	{
		return array(
			'element' => array(static::BELONGS_TO, 'ElementRecord', 'elementId', 'onDelete' => static::CASCADE),
		);
	}
}
