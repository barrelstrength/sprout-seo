<?php
namespace Craft;

/**
 * Class SproutSeo_MetaTagsService
 *
 * @package Craft
 */
class SproutSeo_MetadataService extends BaseApplicationComponent
{
	protected $metadataGroupRecord;

	public function __construct($metaRecord = null)
	{
		$this->metadataGroupRecord = $metaRecord;
		if (is_null($this->metadataGroupRecord))
		{
			$this->metadataGroupRecord = SproutSeo_MetadataGroupRecord::model();
		}
	}

	// Meta Tags Output
	// =========================================================================

	/**
	 * @param $overrideInfo
	 *
	 * @return string
	 */
	public function getMetaTagHtml(SproutSeo_MetadataModel $prioritizedMetaTagModel)
	{
		$globals = sproutSeo()->schema->getGlobals();

		$googlePlus = sproutSeo()->schema->getGooglePlus($globals['social']);

		craft()->templates->setTemplatesPath(craft()->path->getPluginsPath());

		$output = craft()->templates->render('sproutseo/templates/_special/meta', array(
			'globals'    => $globals,
			'meta'       => $prioritizedMetaTagModel->getMetaTagData(),
			'googlePlus' => $googlePlus
		));

		craft()->templates->setTemplatesPath(craft()->path->getSiteTemplatesPath());

		return $output;
	}

	/**
	 * Prioritize our meta data
	 * ------------------------------------------------------------
	 *
	 * Loop through and select the highest ranking value for each attribute in our SproutSeo_MetaData model
	 *
	 * 1) Entry Override (Set by adding `id` override in Twig template code and using Meta Fields)
	 * 2) On-Page Override (Set in Twig template code)
	 * 3) Default (Set in control panel by url Metadata option)
	 * 4) Global Fallback (Set in control panel)
	 * 5) Blank (Automatic)
	 *
	 * Once we have added all the content we need to be outputting to our array we will loop through that array and create the HTML we will output to our page.
	 *
	 * While we don't define HTML in our PHP as much as possible, the goal here is to be as easy to use as possible on the front end so we want to simplify the front end code to a single function and wrangle what we need to here.
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function getPrioritizedMetaTagModel($sitemapInfo)
	{
		$metaLevels = SproutSeo_MetaTagLevels::getConstants();

		foreach ($metaLevels as $key => $metaLevel)
		{
			$prioritizeMetaLevels[$metaLevel] = null;
		}

		$prioritizedMetaTagModel = new SproutSeo_MetadataModel();

		sproutSeo()->optimize->divider = craft()->plugins->getPlugin('sproutseo')->getSettings()->seoDivider;

		// Default to the Current URL
		$prioritizedMetaTagModel->canonical  = SproutSeoOptimizeHelper::prepareCanonical($prioritizedMetaTagModel);
		$prioritizedMetaTagModel->ogUrl      = SproutSeoOptimizeHelper::prepareCanonical($prioritizedMetaTagModel);
		$prioritizedMetaTagModel->twitterUrl = SproutSeoOptimizeHelper::prepareCanonical($prioritizedMetaTagModel);

		foreach ($prioritizeMetaLevels as $meta => $model)
		{
			$metaTagModel = new SproutSeo_MetadataModel();

			$metaTagModel = $metaTagModel->setMeta($meta, $this->getMetaTagsFromTemplate(
				$meta, $sitemapInfo)
			);

			$prioritizeMetaLevels[$meta] = $metaTagModel;

			foreach ($prioritizedMetaTagModel->getAttributes() as $key => $value)
			{
				// Test for a value on each of our models in their order of priority
				if ($metaTagModel->getAttribute($key))
				{
					$prioritizedMetaTagModel[$key] = $metaTagModel[$key];
				}

				// Make sure all our strings are trimmed
				if (is_string($prioritizedMetaTagModel[$key]))
				{
					$prioritizedMetaTagModel[$key] = trim($prioritizedMetaTagModel[$key]);
				}
			}
		}

		$prioritizedMetaTagModel->title = SproutSeoOptimizeHelper::prepareAppendedSiteName(
			$prioritizedMetaTagModel,
			$prioritizeMetaLevels[SproutSeo_MetaTagLevels::MetadataGroup],
			$prioritizeMetaLevels[SproutSeo_MetaTagLevels::GlobalFallback]
		);

		$prioritizedMetaTagModel->robots = SproutSeoOptimizeHelper::getRobotsMetaValue($prioritizedMetaTagModel->robots);

		return $prioritizedMetaTagModel;
	}

	/**
	 * Store our template meta data in a place so we can access when we need to
	 *
	 * @return array
	 */
	public function getMetaTagsFromTemplate($type = null, $sitemapInfo)
	{
		$response = array();

		switch ($type)
		{
			case SproutSeo_MetaTagLevels::MetadataGroup:
				if (isset($sitemapInfo['elementTable']) && isset($sitemapInfo['elementGroupId']))
				{
					$response = $sitemapInfo;
				}
				break;
			case SproutSeo_MetaTagLevels::Entry:
				if (isset($sitemapInfo['elementModel']))
				{
					$elementModel = $sitemapInfo['elementModel'];

					if (isset($elementModel->id))
					{
						$response = array('elementId' => $elementModel->id);
					}
				}
				break;
			case SproutSeo_MetaTagLevels::Code:
				$response = sproutSeo()->optimize->templateMeta;
				break;
		}

		return $response;
	}

	// Global Meta Tags
	// =========================================================================

	/**
	 * Get all Metadata Groups from the database.
	 *
	 * @return array
	 */
	public function getMetaTagGroups()
	{
		$results = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metadatagroups')
			->order('name')
			->queryAll();

		return SproutSeo_MetadataModel::populateModels($results);
	}

	/**
	 * Get all Metadata Groups from the database.
	 *
	 * @return array
	 */
	public function getCustomMetaTagGroups($urls)
	{
		$results = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metadatagroups')
			->where(array('not in', 'url', $urls))
			->order('name')
			->queryAll();

		return SproutSeo_MetadataModel::populateModels($results);
	}

	/**
	 * Get a specific Metadata Group from the database based on ID
	 *
	 * @param $id
	 *
	 * @return BaseModel|SproutSeo_MetadataModel
	 */
	public function getMetadataGroupById($id)
	{
		if ($record = $this->metadataGroupRecord->findByPk($id))
		{
			return SproutSeo_MetadataModel::populateModel($record);
		}

		return new SproutSeo_MetadataModel();
	}

	/**
	 * @param $handle
	 *
	 * @return BaseModel|SproutSeo_MetadataModel
	 */
	public function getMetadataGroupByHandle($handle)
	{
		$query = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metadatagroups')
			->where('handle=:handle', array(':handle' => $handle))
			->queryRow();

		if (!isset($query))
		{
			return new SproutSeo_MetadataModel();
		}

		$model = SproutSeo_MetadataModel::populateModel($query);

		$model->robots   = ($model->robots) ? SproutSeoOptimizeHelper::prepRobotsForSettings($model->robots) : null;
		$model->position = SproutSeoOptimizeHelper::prepareGeoPosition($model);

		return $model;
	}

	/**
	 * @param $url
	 *
	 * @return BaseModel|SproutSeo_MetadataModel
	 */
	public function getMetadataGroupByInfo($type, $elementGroupId)
	{
		$metadataGroup = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metadatagroups')
			->where('type=:type and elementGroupId=:elementGroupId',
				array(':type' => $type, ':elementGroupId' => $elementGroupId)
			)
			->queryRow();

		$model = new SproutSeo_MetadataModel();

		if ($metadataGroup)
		{
			$model = SproutSeo_MetadataModel::populateModel($metadataGroup);
		}

		$model->robots   = ($model->robots) ? SproutSeoOptimizeHelper::prepRobotsForSettings($model->robots) : null;
		$model->position = SproutSeoOptimizeHelper::prepareGeoPosition($model);

		return $model;
	}

	/**
	 * @param SproutSeo_MetadataModel $model
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function saveMetadataGroup(SproutSeo_MetadataModel $model)
	{
		if ($id = $model->getAttribute('id'))
		{
			if (null === ($record = $this->metadataGroupRecord->findByPk($id)))
			{
				throw new Exception(Craft::t('Can\'t find default with ID "{id}"', array(
					'id' => $id
				)));
			}
		}
		else
		{
			$record = $this->metadataGroupRecord->create();
		}

		// @todo - is there a better way to do this flip/flop?
		$model->dateUpdated = $record->dateUpdated;
		$model->dateCreated = $record->dateCreated;
		$model->uid         = $record->uid;

		$record->setAttributes($model->getAttributes(), false);
		$record->dateUpdated = $model->dateUpdated;
		$record->dateCreated = $model->dateCreated;
		$record->uid         = $model->uid;

		if ($record->save())
		{
			// update id on model (for new records)
			$model->setAttribute('id', $record->getAttribute('id'));

			return true;
		}
		else
		{
			$model->addErrors($record->getErrors());

			return false;
		}
	}

	/**
	 * Delete a Metadata Group by ID
	 *
	 * @param int
	 *
	 * @return bool
	 */
	public function deleteMetadataGroupById($id = null)
	{
		$record = new SproutSeo_MetadataGroupRecord;

		return $record->deleteByPk($id);
	}

	// Meta Tag Content
	// =========================================================================

	/**
	 * Create a new Meta Tag Content record
	 *
	 * @param $attributes
	 */
	public function createMetadataContent($attributes)
	{
		craft()->db->createCommand()
			->insert('sproutseo_metadatacontent', $attributes);
	}

	/**
	 * Get a Meta Tag Content record by Entry ID
	 *
	 * @param $elementId
	 * @param $locale
	 *
	 * @return BaseModel
	 */
	public function getMetadataContentByElementId($elementId, $locale)
	{
		$query = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metadatacontent')
			->where('elementId = :elementId', array(':elementId' => $elementId))
			->andWhere('locale = :locale', array(':locale' => $locale))
			->queryRow();

		$model = SproutSeo_MetadataModel::populateModel($query);

		return $model;
	}

	/**
	 * Update a Meta Tag Content record
	 *
	 * @param $id
	 * @param $attributes
	 */
	public function updateMetadataContent($id, $attributes)
	{
		craft()->db->createCommand()
			->update('sproutseo_metadatacontent',
				$attributes,
				'id = :id', array(':id' => $id)
			);
	}

	/**
	 * Delete a Meta Tag Content record
	 *
	 * @param null $id
	 *
	 * @return int
	 */
	public function deleteMetadataContentById($id = null)
	{
		$record = new SproutSeo_MetadataContentRecord();

		return $record->deleteByPk($id);
	}

	/**
	 * Returns metadata group info
	 *
	 * @param array $info
	 *
	 * @return array
	 */
	public function getMetadataInfo($info)
	{
		$response = array(
			'element'    => null,
			'isNew'      => true,
			'metadataId' => ''
		);

		$element = null;

		if ($info)
		{
			$type = explode('-', $info['sitemapId']);
			$type = $type[0];

			// Just trying to get the url
			$sitemaps    = craft()->plugins->call('registerSproutSeoSitemap');
			$elementInfo = sproutSeo()->sitemap->getElementInfo($sitemaps, $type);
			$locale      = craft()->i18n->getLocaleById(craft()->language);

			// If we don't have an elementGroupId, we're working with a Custom Metadata Page
			if (isset($elementInfo['elementGroupId']))
			{
				$elementGroup              = $elementInfo['elementGroupId'];
				$criteria                  = craft()->elements->getCriteria($elementInfo['elementType']);
				$criteria->{$elementGroup} = $info['elementGroupId'];

				$criteria->limit   = null;
				$criteria->enabled = true;
				$criteria->locale  = $locale->id;

				// Support one locale for now
				$element = $criteria->find();
			}

			if ($element)
			{
				$element = $element[0];

				// check if exists in sproutseo_metadatagroups
				$metataggroups = $this->getMetadataGroupByInfo($type, $info['elementGroupId']);

				if ($metataggroups->sitemapUrl)
				{
					$response['isNew']        = false;
					$response['metadataId']   = $metataggroups->id;
					$response['metataggroup'] = $metataggroups;
				}
			}
			else
			{
				$element = null;
			}
		}

		$response['element'] = $element;

		return $response;
	}

	public function updateOptimizedAndAdvancedMetaValues($model)
	{
		$globals        = sproutSeo()->schema->getGlobals();
		$globalSettings = $globals->settings;

		// Prepare our optimized variables
		// -------------------------------------------------------------
		$optimizedTitle       = (!empty($model->optimizedTitle) ? $model->optimizedTitle : null);
		$optimizedDescription = (!empty($model->optimizedDescription) ? $model->optimizedDescription : null);

		// Make our images single IDs instead of an array
		$optimizedImage          = (!empty($model->optimizedImage and is_array($model->optimizedImage)) ? $model['optimizedImage'][0] : $model->optimizedImage);
		$model['optimizedImage'] = $optimizedImage;

		// Set null values for any Advanced SEO Optimization
		// override fields whose blocks have been disabled
		// -------------------------------------------------------------
		$customizationSettings = JsonHelper::decode($model->customizationSettings);

		if (!$customizationSettings['basicMetaMetadataGroupEnabled'])
		{
			foreach ($model['basicMeta'] as $attribute => $value)
			{
				$model->{$attribute} = null;
			}
		}

		if (!$customizationSettings['openGraphMetadataGroupEnabled'])
		{
			foreach ($model['openGraphMeta'] as $attribute => $value)
			{
				$model->{$attribute} = null;
			}
		}

		if (!$customizationSettings['twitterCardMetadataGroupEnabled'])
		{
			foreach ($model['twitterCardsMeta'] as $attribute => $value)
			{
				$model->{$attribute} = null;
			}
		}

		if (!$customizationSettings['geoMetadataGroupEnabled'])
		{
			foreach ($model['geographicMeta'] as $attribute => $value)
			{
				$model->{$attribute} = null;
			}
		}

		if (!$customizationSettings['robotsMetadataGroupEnabled'])
		{
			foreach ($model['robotsMeta'] as $attribute => $value)
			{
				$model->{$attribute} = null;
			}
		}

		// Set any values that don't yet exist to the optimized values
		// -------------------------------------------------------------
		$model->title        = !is_null($model->title) ? $model->title : $optimizedTitle;
		$model->ogTitle      = !is_null($model->ogTitle) ? $model->ogTitle : $optimizedTitle;
		$model->twitterTitle = !is_null($model->twitterTitle) ? $model->twitterTitle : $optimizedTitle;

		$model->description        = !is_null($model->description) ? $model->description : $optimizedDescription;
		$model->ogDescription      = !is_null($model->ogDescription) ? $model->ogDescription : $optimizedDescription;
		$model->twitterDescription = !is_null($model->twitterDescription) ? $model->twitterDescription : $optimizedDescription;

		$model->ogImage      = !is_null($model->ogImage) ? $model->ogImage : $optimizedImage;
		$model->twitterImage = !is_null($model->twitterImage) ? $model->twitterImage : $optimizedImage;

		$model->ogType      = !is_null($model->ogType) ? $model->ogType : $globalSettings['defaultOgType'];
		$model->twitterCard = !is_null($model->twitterCard) ? $model->twitterCard : $globalSettings['defaultTwitterCard'];

		return $model;
	}
}
