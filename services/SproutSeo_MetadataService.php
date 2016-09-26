<?php
namespace Craft;

/**
 * Class SproutSeo_MetaTagsService
 *
 * @package Craft
 */
class SproutSeo_MetadataService extends BaseApplicationComponent
{
	/**
	 * @var BaseRecord|object
	 */
	protected $sectionMetadataRecord;

	/**
	 * SproutSeo_MetadataService constructor.
	 *
	 * @param null $metaRecord
	 */
	public function __construct($metaRecord = null)
	{
		$this->sectionMetadataRecord = $metaRecord;
		if (is_null($this->sectionMetadataRecord))
		{
			$this->sectionMetadataRecord = SproutSeo_SectionMetadataRecord::model();
		}
	}

	// Section Metadata
	// =========================================================================

	/**
	 * Get all Section Metadata from the database.
	 *
	 * @return array
	 */
	public function getSectionMetadata()
	{
		$results = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metadata_sections')
			->order('name')
			->queryAll();

		return SproutSeo_MetadataModel::populateModels($results);
	}

	/**
	 * Get a specific Section Metadata from the database based on ID
	 *
	 * @param $id
	 *
	 * @return BaseModel|SproutSeo_MetadataModel
	 */
	public function getSectionMetadataById($id)
	{
		if ($record = $this->sectionMetadataRecord->findByPk($id))
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
	public function getSectionMetadataByHandle($handle)
	{
		$query = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metadata_sections')
			->where('handle=:handle', array(':handle' => $handle))
			->queryRow();

		if (!isset($query))
		{
			return new SproutSeo_MetadataModel();
		}

		$model = SproutSeo_MetadataModel::populateModel($query);

		$model->robots   = ($model->robots) ? SproutSeoOptimizeHelper::prepareRobotsMetadataForSettings($model->robots) : null;
		$model->position = SproutSeoOptimizeHelper::prepareGeoPosition($model);

		return $model;
	}

	/**
	 * @param $url
	 *
	 * @return BaseModel|SproutSeo_MetadataModel
	 */
	public function getSectionMetadataByInfo($type, $elementGroupId, $elementModel = null)
	{
		$sectionMetadata = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metadata_sections')
			->where('type=:type and elementGroupId=:elementGroupId',
				array(':type' => $type, ':elementGroupId' => $elementGroupId)
			)
			->queryRow();

		$model = new SproutSeo_MetadataModel();

		if ($sectionMetadata)
		{
			$model = SproutSeo_MetadataModel::populateModel($sectionMetadata);
		}

		$model->robots   = ($model->robots) ? SproutSeoOptimizeHelper::prepareRobotsMetadataForSettings($model->robots) : null;
		$model->position = SproutSeoOptimizeHelper::prepareGeoPosition($model);

		if (craft()->request->isSiteRequest())
		{
			//Craft::dd($model->optimizedTitle);
			//$model->optimizedTitle = craft()->templates->renderObjectTemplate($model->optimizedTitle, $elementModel);
			//Craft::dd($model->optimizedTitle);
		}

		return $model;
	}

	/**
	 * Get all Section Metadata from the database.
	 *
	 * @return array
	 */
	public function getCustomSectionMetadata($urls)
	{
		$results = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metadata_sections')
			->where(array('not in', 'url', $urls))
			->order('name')
			->queryAll();

		return SproutSeo_MetadataModel::populateModels($results);
	}

	/**
	 * @param SproutSeo_MetadataModel $model
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function saveSectionMetadata(SproutSeo_MetadataModel $model)
	{
		if ($id = $model->getAttribute('id'))
		{
			if (null === ($record = $this->sectionMetadataRecord->findByPk($id)))
			{
				throw new Exception(Craft::t('Can\'t find default with ID "{id}"', array(
					'id' => $id
				)));
			}
		}
		else
		{
			$record = $this->sectionMetadataRecord->create();
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
	 * Delete a Section Metadata by ID
	 *
	 * @param int
	 *
	 * @return bool
	 */
	public function deleteSectionMetadataById($id = null)
	{
		$record = new SproutSeo_SectionMetadataRecord();

		return $record->deleteByPk($id);
	}

	/**
	 * Returns Section Metadata Info
	 *
	 * @todo - clarify what "info" is
	 *
	 * @param array $info
	 *
	 * @return array
	 */
	public function getSectionMetadataInfo($info)
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
			$elementInfo = sproutSeo()->sitemap->getSectionMetadataElementInfo($sitemaps, $type);
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

				// check if exists in sproutseo_metadata_sections
				$sectionMetadata = $this->getSectionMetadataByInfo($type, $info['elementGroupId']);

				if ($sectionMetadata->url)
				{
					$response['isNew']           = false;
					$response['metadataId']      = $sectionMetadata->id;
					$response['sectionmetadata'] = $sectionMetadata;
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

	// Element Metadata
	// =========================================================================

	/**
	 * Create an Element Metadata record
	 *
	 * @param $attributes
	 */
	public function createElementMetadata($attributes)
	{
		craft()->db->createCommand()
			->insert('sproutseo_metadata_elements', $attributes);
	}

	/**
	 * Get an Element Metadata by Element ID
	 *
	 * @param $elementId
	 * @param $locale
	 *
	 * @return BaseModel
	 */
	public function getElementMetadataByElementId($elementId, $locale)
	{
		$query = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metadata_elements')
			->where('elementId = :elementId', array(':elementId' => $elementId))
			->andWhere('locale = :locale', array(':locale' => $locale))
			->queryRow();

		$model = SproutSeo_MetadataModel::populateModel($query);

		return $model;
	}

	/**
	 * Update am Element Metadata record
	 *
	 * @param $id
	 * @param $attributes
	 */
	public function updateElementMetadata($id, $attributes)
	{
		craft()->db->createCommand()
			->update('sproutseo_metadata_elements',
				$attributes,
				'id = :id', array(':id' => $id)
			);
	}

	/**
	 * Delete an Element Metadata record
	 *
	 * @param null $id
	 *
	 * @return int
	 */
	public function deleteElementMetadataById($id = null)
	{
		$record = new SproutSeo_ElementMetadataRecord();

		return $record->deleteByPk($id);
	}

	// Code Metadata
	// =========================================================================

	/**
	 * Store our codeMetadata in a place so we can access when we need to
	 *
	 * @return array
	 */
	public function getCodeMetadata($type = null, $sitemapInfo)
	{
		$response = array();

		switch ($type)
		{
			case SproutSeo_MetadataLevels::SectionMetadata:
				if (isset($sitemapInfo['elementTable']) && isset($sitemapInfo['elementGroupId']))
				{
					$response = $sitemapInfo;
				}
				break;
			case SproutSeo_MetadataLevels::ElementMetadata:
				if (isset($sitemapInfo['elementModel']))
				{
					$elementModel = $sitemapInfo['elementModel'];

					if (isset($elementModel->id))
					{
						$response = array('elementId' => $elementModel->id);
					}
				}
				break;
			case SproutSeo_MetadataLevels::CodeMetadata:
				$response = sproutSeo()->optimize->codeMetadata;
				break;
		}

		return $response;
	}
}
