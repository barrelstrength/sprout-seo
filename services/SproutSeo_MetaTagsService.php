<?php
namespace Craft;

/**
 * Class SproutSeo_MetaTagsService
 *
 * @package Craft
 */
class SproutSeo_MetaTagsService extends BaseApplicationComponent
{
	protected $metaRecord;

	public function __construct($metaRecord = null)
	{
		$this->metaRecord = $metaRecord;
		if (is_null($this->metaRecord))
		{
			$this->metaRecord = SproutSeo_MetaTagGroupRecord::model();
		}
	}

	// Meta Tags Output
	// =========================================================================

	/**
	 * @param $overrideInfo
	 *
	 * @return string
	 */
	public function getMetaTagHtml()
	{
		$globals = sproutSeo()->schema->getGlobals();

		$prioritizedMetaTagModel = $this->getOptimizedMeta();

		craft()->templates->setTemplatesPath(craft()->path->getPluginsPath());

		$output = craft()->templates->render('sproutseo/templates/_special/meta', array(
			'globals' => $globals,
			'meta'    => $prioritizedMetaTagModel->getMetaTagData()
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
	 * 3) Default (Set in control panel)
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
	public function getOptimizedMeta()
	{
		$metaLevels = SproutSeo_MetaLevels::getConstants();

		foreach ($metaLevels as $key => $metaLevel)
		{
			$prioritizeMetaLevels[$metaLevel] = null;
		}

		$prioritizedMetaTagModel = new SproutSeo_MetaTagsModel();

		sproutSeo()->optimize->divider = craft()->plugins->getPlugin('sproutseo')->getSettings()->seoDivider;

		// Default to the Current URL
		$prioritizedMetaTagModel->canonical  = SproutSeoOptimizeHelper::prepareCanonical($prioritizedMetaTagModel);
		$prioritizedMetaTagModel->ogUrl      = SproutSeoOptimizeHelper::prepareCanonical($prioritizedMetaTagModel);
		$prioritizedMetaTagModel->twitterUrl = SproutSeoOptimizeHelper::prepareCanonical($prioritizedMetaTagModel);

		foreach ($prioritizeMetaLevels as $meta => $model)
		{
			$metaTagModel = new SproutSeo_MetaTagsModel();

			$metaTagModel = $metaTagModel->setMeta($meta, $this->getMetaTagsFromTemplate($meta));

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

		// @todo - reorganize how this stuff works / robots need love.
		$prioritizedMetaTagModel->title = SproutSeoOptimizeHelper::prepareAppendedSiteName(
			$prioritizedMetaTagModel,
			$prioritizeMetaLevels[SproutSeo_MetaLevels::MetaTagsGroup],
			$prioritizeMetaLevels[SproutSeo_MetaLevels::Global],
			$prioritizeMetaLevels[SproutSeo_MetaLevels::Entry]
		);

		$prioritizedMetaTagModel->robots = SproutSeoOptimizeHelper::prepRobotsAsString($prioritizedMetaTagModel->robots);

		return $prioritizedMetaTagModel;
	}

	/**
	 * Store our template meta data in a place so we can access when we need to
	 *
	 * @return array
	 */
	public function getMetaTagsFromTemplate($type = null)
	{
		$entry = isset(sproutSeo()->optimize->context['entry']) ? sproutSeo()->optimize->context['entry'] : null;

		switch ($type)
		{
			case SproutSeo_MetaLevels::MetaTagsGroup:
				if ($entry)
				{
					$slug = $entry->slug;

					sproutSeo()->optimize->templateMeta = array('slug' => $slug);
				}
				break;
			case SproutSeo_MetaLevels::Entry:
				if ($entry)
				{
					//this will support any element
					sproutSeo()->optimize->templateMeta = array('entryId' => $entry->id);
				}
				break;
		}

		return sproutSeo()->optimize->templateMeta;
	}

	// Global Meta Tags
	// =========================================================================

	/**
	 * Get all Meta Tag Groups from the database.
	 *
	 * @return array
	 */
	public function getMetaTagGroups()
	{
		$results = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metataggroups')
			->order('name')
			->queryAll();

		return SproutSeo_MetaTagsModel::populateModels($results);
	}

	/**
	 * Get a specific Meta Tag Group from the database based on ID
	 *
	 * @param $id
	 *
	 * @return BaseModel|SproutSeo_MetaTagsModel
	 */
	public function getMetaTagGroupById($id)
	{
		if ($record = $this->metaRecord->findByPk($id))
		{
			return SproutSeo_MetaTagsModel::populateModel($record);
		}
		else
		{
			return new SproutSeo_MetaTagsModel();
		}
	}

	/**
	 * @param $handle
	 *
	 * @return BaseModel|SproutSeo_MetaTagsModel
	 */
	public function getMetaTagGroupByHandle($handle)
	{
		$query = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metataggroups')
			->where('handle=:handle', array(':handle' => $handle))
			->queryRow();

		if (isset($query))
		{
			$model = SproutSeo_MetaTagsModel::populateModel($query);
		}
		else
		{
			return new SproutSeo_MetaTagsModel();
		}

		$model->robots   = ($model->robots) ? SproutSeoOptimizeHelper::prepRobotsForSettings($model->robots) : null;
		$model->position = SproutSeoOptimizeHelper::prepareGeoPosition($model);

		return $model;
	}

	/**
	 * @param $url
	 *
	 * @return BaseModel|SproutSeo_MetaTagsModel
	 */
	public function getMetaTagGroupByUrl($url)
	{
		$query = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metataggroups')
			->where('url=:url', array(':url' => $url))
			->queryRow();

		if (isset($query))
		{
			$model = SproutSeo_MetaTagsModel::populateModel($query);
		}
		else
		{
			return new SproutSeo_MetaTagsModel();
		}

		$model->robots   = ($model->robots) ? SproutSeoOptimizeHelper::prepRobotsForSettings($model->robots) : null;
		$model->position = SproutSeoOptimizeHelper::prepareGeoPosition($model);

		return $model;
	}

	/**
	 * @param SproutSeo_MetaTagsModel $model
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function saveMetaTagGroup(SproutSeo_MetaTagsModel $model)
	{
		if ($id = $model->getAttribute('id'))
		{
			if (null === ($record = $this->metaRecord->findByPk($id)))
			{
				throw new Exception(Craft::t('Can\'t find default with ID "{id}"', array(
					'id' => $id
				)));
			}
		}
		else
		{
			$record = $this->metaRecord->create();
		}

		// @todo - Can we improve how validation is handled here?
		// Setting the second argument to 'false' allows us to save unsafe attributes
		$record->setAttributes($model->getAttributes(), false);

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
	 * Delete a Meta Tag Group by ID
	 *
	 * @param int
	 *
	 * @return bool
	 */
	public function deleteMetaTagGroupById($id = null)
	{
		$record = new SproutSeo_MetaTagGroupRecord;

		return $record->deleteByPk($id);
	}

	// Meta Tag Content
	// =========================================================================

	/**
	 * Create a new Meta Tag Content record
	 *
	 * @param $attributes
	 */
	public function createMetaTagContent($attributes)
	{
		craft()->db->createCommand()
			->insert('sproutseo_metatagcontent', $attributes);
	}

	/**
	 * Get a Meta Tag Content record by Entry ID
	 *
	 * @param $entryId
	 * @param $locale
	 *
	 * @return BaseModel
	 */
	public function getMetaTagContentByEntryId($entryId, $locale)
	{
		$query = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metatagcontent')
			->where('entryId = :entryId', array(':entryId' => $entryId))
			->andWhere('locale = :locale', array(':locale' => $locale))
			->queryRow();

		$model = SproutSeo_MetaTagsModel::populateModel($query);

		return $model;
	}

	/**
	 * Update a Meta Tag Content record
	 *
	 * @param $id
	 * @param $attributes
	 */
	public function updateMetaTagContent($id, $attributes)
	{
		craft()->db->createCommand()
			->update('sproutseo_metatagcontent',
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
	public function deleteMetaTagContentById($id = null)
	{
		$record = new SproutSeo_MetaTagContentRecord();

		return $record->deleteByPk($id);
	}
}
