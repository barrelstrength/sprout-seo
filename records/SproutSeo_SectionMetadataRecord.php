<?php
namespace Craft;

class SproutSeo_SectionMetadataRecord extends BaseRecord
{
	/**
	 * @return string
	 */
	public function getTableName()
	{
		return 'sproutseo_metadata_sections';
	}

	/**
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
			'name'                => array(AttributeType::String, 'required' => true),
			'handle'              => array(AttributeType::String, 'required' => true),
			'enabled'             => array(AttributeType::Bool, 'default' => false, 'required' => true),

			// sitemap data
			'urlEnabledSectionId' => array(AttributeType::Number),
			'type'                => array(AttributeType::String),
			'priority'            => array(AttributeType::Number, 'maxLength' => 2, 'decimals' => 1, 'default' => '0.5', 'required' => true),
			'changeFrequency'     => array(AttributeType::String, 'maxLength' => 7, 'default' => 'weekly', 'required' => true),
			'url'                 => array(AttributeType::String),
			'isCustom'            => array(AttributeType::Bool, 'default' => false, 'required' => true),
			// end sitemap

			'optimizedTitle'        => array(AttributeType::String),
			'optimizedDescription'  => array(AttributeType::String),
			'optimizedImage'        => array(AttributeType::String),
			'optimizedKeywords'     => array(AttributeType::String),
			'customizationSettings' => array(AttributeType::String),

			'title'            => array(AttributeType::String),
			'appendTitleValue' => array(AttributeType::String),
			'description'      => array(AttributeType::String),
			'keywords'         => array(AttributeType::String),
			'author'           => array(AttributeType::String),
			'publisher'        => array(AttributeType::String),

			'robots'    => array(AttributeType::String),
			'canonical' => array(AttributeType::String),

			'region'    => array(AttributeType::String),
			'placename' => array(AttributeType::String),
			'latitude'  => array(AttributeType::String),
			'longitude' => array(AttributeType::String),

			'ogTitle'       => array(AttributeType::String),
			'ogType'        => array(AttributeType::String),
			'ogUrl'         => array(AttributeType::String),
			'ogImage'       => array(AttributeType::String),
			'ogTransform'   => array(AttributeType::String),
			'ogAuthor'      => array(AttributeType::String),
			'ogPublisher'   => array(AttributeType::String),
			'ogSiteName'    => array(AttributeType::String),
			'ogDescription' => array(AttributeType::String),
			'ogAudio'       => array(AttributeType::String),
			'ogVideo'       => array(AttributeType::String),
			'ogLocale'      => array(AttributeType::String),

			'twitterCard'        => array(AttributeType::String),
			'twitterSite'        => array(AttributeType::String),
			'twitterTitle'       => array(AttributeType::String),
			'twitterCreator'     => array(AttributeType::String),
			'twitterDescription' => array(AttributeType::String),

			'twitterUrl'                     => array(AttributeType::String),
			'twitterImage'                   => array(AttributeType::String),
			'twitterTransfrom'               => array(AttributeType::String),

			// Fields for Twitter Player Card
			'twitterPlayer'                  => array(AttributeType::String),
			'twitterPlayerStream'            => array(AttributeType::String),
			'twitterPlayerStreamContentType' => array(AttributeType::String),
			'twitterPlayerWidth'             => array(AttributeType::String),
			'twitterPlayerHeight'            => array(AttributeType::String),

			// Structured Data
			'schemaTypeId'                   => array(AttributeType::String),
			'schemaOverrideTypeId'           => array(AttributeType::String),
		);
	}

	/**
	 * @return array
	 */
	public function defineIndexes()
	{
		return array(
			array(
				'columns' => array(
					'name',
					'handle'
				),
				'unique'  => true
			),
		);
	}

	/**
	 * Create a new instance of the current class. This allows us to
	 * properly unit test our service layer.
	 *
	 * @return BaseRecord
	 */
	public function create()
	{
		$class  = get_class($this);
		$record = new $class();

		return $record;
	}
}
