<?php
namespace Craft;

class SproutSeo_TemplatesRecord extends BaseRecord
{
	public function getTableName()
	{
		return 'sproutseo_templates';
	}

	protected function defineAttributes()
	{
		return array(
			'name' => array(
				AttributeType::String,
				'required' => true
			),
			'handle' => array(
				AttributeType::String,
				'required' => true
			),
			'appendSiteName' => array(AttributeType::String),
			'globalFallback' => array(AttributeType::Bool),

			'title'          => array(AttributeType::String),
			'description'    => array(AttributeType::String),
			'keywords'       => array(AttributeType::String),

			'robots'         => array(AttributeType::String),
			'canonical'      => array(AttributeType::String),

			'region'         => array(AttributeType::String),
			'placename'      => array(AttributeType::String),
			'latitude'       => array(AttributeType::String),
			'longitude'      => array(AttributeType::String),

			'ogTitle'        => array(AttributeType::String),
			'ogType'         => array(AttributeType::String),
			'ogUrl'          => array(AttributeType::String),
			'ogImage'        => array(AttributeType::String),
			'ogSiteName'     => array(AttributeType::String),
			'ogDescription'  => array(AttributeType::String),
			'ogAudio'        => array(AttributeType::String),
			'ogVideo'        => array(AttributeType::String),
			'ogLocale'       => array(AttributeType::String),

			// Store the Twitter Card Type
			// @TODO convert to enum with the proper choices
			'twitterCard'        => array(AttributeType::String),
			'twitterSite'        => array(AttributeType::String),
			'twitterTitle'       => array(AttributeType::String),
			'twitterCreator'     => array(AttributeType::String),
			'twitterDescription' => array(AttributeType::String),

			// Fields for Twitter Summary Card
			'twitterSummaryImageSource' => array(AttributeType::String),

			// Fields for Twitter Summary Large Image Card
			'twitterSummaryLargeImageImageSource' => array(AttributeType::String),

			// Fields for Twitter Photo Card
			'twitterPhotoImageSource' => array(AttributeType::String,),

			// Fields for Twitter Player Card
			'twitterPlayerImageSource'       => array(AttributeType::String,),
			'twitterPlayer'                  => array(AttributeType::String,),
			'twitterPlayerStream'            => array(AttributeType::String,),
			'twitterPlayerStreamContentType' => array(AttributeType::String,),
			'twitterPlayerWidth'             => array(AttributeType::String,),
			'twitterPlayerHeight'            => array(AttributeType::String,),

		);
	}

	public function defineIndexes()
	{
		return array(
			array(
				'columns' => array(
					'name',
					'handle'
					),
				'unique' => true
			),
		);
	}

	// @TODO implement assets for field types
	// public function defineRelations()
	// {
	//     return array(
	//         'asset'    => array(static::BELONGS_TO, 'AssetFileRecord'),
	//     );
	// }

	/**
	 * Create a new instance of the current class. This allows us to
	 * properly unit test our service layer.
	 *
	 * @return BaseRecord
	 */
	public function create()
	{
		$class = get_class($this);
		$record = new $class();

		return $record;
	}
}
