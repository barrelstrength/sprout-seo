<?php
namespace Craft;

/**
 * Class SproutSeo_MetaDefaultsService
 *
 * @package Craft
 */
class SproutSeo_MetaDefaultsService extends BaseApplicationComponent
{
	protected $metaRecord;

	public function __construct($metaRecord = null)
	{
		$this->metaRecord = $metaRecord;
		if (is_null($this->metaRecord)) {
			$this->metaRecord = SproutSeo_DefaultsRecord::model();
		}
	}

	/**
	 * Get all Defaults from the database.
	 *
	 * @return array
	 */
	public function getDefaults()
	{
		$defaults = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_defaults')
			->order('name')
			->queryAll();

		$model = SproutSeo_MetaModel::populateModels($defaults);

		return $model;
	}

	/**
	 * Get a specific Defaults from the database based on ID. If no Defaults
	 * exists, null is returned.
	 *
	 * @param  int   $id
	 * @return mixed
	 */
	public function getDefaultById($id)
	{
		if ($record = $this->metaRecord->findByPk($id))
		{
			return SproutSeo_MetaModel::populateModel($record);
		}
		else
		{
			return new SproutSeo_MetaModel();
		}
	}

	/**
	 * @param $handle
	 * @return BaseModel|SproutSeo_MetaModel
	 */
	public function getDefaultByHandle($handle)
	{
		$query = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_defaults')
			->where('handle=:handle', array(':handle'=> $handle))
			->queryRow();

		if (isset($query))
		{
			$model = SproutSeo_MetaModel::populateModel($query);
		}
		else
		{
			return new SproutSeo_MetaModel();
		}

		$model->robots = ($model->robots) ? SproutSeoMetaHelper::prepRobotsForSettings($model->robots) : null;
		$model->position = SproutSeoMetaHelper::prepareGeoPosition($model);

		return $model;
	}

	/**
	 * @param SproutSeo_MetaModel $model
	 * @return bool
	 * @throws Exception
	 */
	public function saveDefault(SproutSeo_MetaModel $model)
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
	 * Deletes a default
	 *
	 * @param int
	 * @return bool
	 */
	public function deleteDefault($id = null)
	{
		$record = new SproutSeo_DefaultsRecord;
		return $record->deleteByPk($id);
	}

	/**
	 * Determines if a global fallback setting already exists
	 *
	 * @return id | null
	 */
	public function globalFallbackId()
	{
		$globalFallbackMetaModel = new SproutSeo_MetaModel();
		$globalFallbackMetaModel->setMeta('fallback');

		if ($globalFallbackMetaModel->id)
		{
			return $globalFallbackMetaModel->id;
		}

		return null;
	}
}
