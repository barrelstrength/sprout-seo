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

	/**
	 * Determines if a global fallback setting already exists
	 *
	 * @return id | null
	 */
	public function globalFallbackId()
	{
		$globalFallbackMetaTagModel = new SproutSeo_MetaTagsModel();
		$globalFallbackMetaTagModel->setMeta('global');

		if ($globalFallbackMetaTagModel->id)
		{
			return $globalFallbackMetaTagModel->id;
		}

		return null;
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
