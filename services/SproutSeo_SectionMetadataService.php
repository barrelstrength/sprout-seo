<?php
namespace Craft;

/**
 * Class SproutSeo_SectionMetadataService
 *
 * @package Craft
 */
class SproutSeo_SectionMetadataService extends BaseApplicationComponent
{
	/**
	 * @var
	 */
	public $urlEnabledSectionTypes;

	/**
	 * @var BaseRecord|object
	 */
	protected $sectionMetadataRecord;

	/**
	 * SproutSeo_SectionsService constructor.
	 *
	 * @param null $sectionMetadataRecord
	 */
	public function __construct($sectionMetadataRecord = null)
	{
		$this->prepareUrlEnabledSectionTypes();

		$this->sectionMetadataRecord = $sectionMetadataRecord;
		if (is_null($this->sectionMetadataRecord))
		{
			$this->sectionMetadataRecord = SproutSeo_SectionMetadataRecord::model();
		}
	}

	/**
	 * Prepare the $this->urlEnabledSectionTypes variable for use in Sections and Sitemap pages
	 */
	public function prepareUrlEnabledSectionTypes()
	{
		$registeredUrlEnabledSectionsTypes = craft()->plugins->call('registerSproutSeoUrlEnabledSectionTypes');

		foreach ($registeredUrlEnabledSectionsTypes as $plugin => $urlEnabledSectionTypes)
		{
			/**
			 * @var SproutSeoBaseUrlEnabledSectionType $urlEnabledSectionType
			 */
			foreach ($urlEnabledSectionTypes as $urlEnabledSectionType)
			{
				$sectionMetadataSections = $urlEnabledSectionType->getAllSectionMetadataSections();
				$allUrlEnabledSections   = $urlEnabledSectionType->getAllUrlEnabledSections();

				// Prepare a list of all URL-Enabled Sections for this URL-Enabled Section Type
				// if we have an existing Section Metadata, use it, otherwise fallback to a new model
				$urlEnabledSections = array();

				/**
				 * @var SproutSeo_UrlEnabledSectionModel $urlEnabledSection
				 */
				foreach ($allUrlEnabledSections as $urlEnabledSection)
				{
					$uniqueKey = $urlEnabledSectionType->getId() . '-' . $urlEnabledSection->id;

					$model = new SproutSeo_UrlEnabledSectionModel();

					if (isset($sectionMetadataSections[$uniqueKey]))
					{
						// If an URL-Enabled Section exists as section metadata, use it
						$sectionMetadata     = $sectionMetadataSections[$uniqueKey];
						$sectionMetadata->id = $sectionMetadataSections[$uniqueKey]->id;
					}
					else
					{
						// If no URL-Enabled Section exists, create a new one
						$sectionMetadata                      = new SproutSeo_MetadataModel();
						$sectionMetadata->isNew               = true;
						$sectionMetadata->urlEnabledSectionId = $urlEnabledSection->id;
					}

					$model->type = $urlEnabledSectionType;
					$model->id   = $urlEnabledSection->id;

					$sectionMetadata->name    = $urlEnabledSection->name;
					$sectionMetadata->handle  = $urlEnabledSection->handle;
					$sectionMetadata->url     = $model->getUrlFormat();
					$sectionMetadata->hasUrls = $urlEnabledSection->hasUrls;

					$model->sectionMetadata = $sectionMetadata;

					$urlEnabledSections[$uniqueKey] = $model;
				}

				$urlEnabledSectionType->urlEnabledSections = $urlEnabledSections;

				$this->urlEnabledSectionTypes[$urlEnabledSectionType->getId()] = $urlEnabledSectionType;
			}
		}
	}

	/**
	 * Get all registered Element Groups via hook
	 *
	 * @return array
	 */
	public function getUrlEnabledSectionTypes()
	{
		return $this->urlEnabledSectionTypes;
	}

	/**
	 * @param $context
	 *
	 * @return mixed
	 */
	public function getUrlEnabledSectionsViaContext($context)
	{
		foreach ($this->urlEnabledSectionTypes as $urlEnabledSectionType)
		{
			$matchedElementVariable = $urlEnabledSectionType->getMatchedElementVariable();

			if (isset($context[$matchedElementVariable]))
			{
				// Add the current page load matchedElementVariable to our Element Group
				$element = $context[$matchedElementVariable];

				$type = $urlEnabledSectionType->getId();

				$urlEnabledSectionIdColumn = $urlEnabledSectionType->getUrlEnabledSectionIdColumnName();
				$urlEnabledSectionId       = $element->{$urlEnabledSectionIdColumn};

				$uniqueKey = $type . '-' . $urlEnabledSectionId;

				$urlEnabledSection          = $urlEnabledSectionType->urlEnabledSections[$uniqueKey];
				$urlEnabledSection->element = $element;

				return $urlEnabledSection;
			}
		}
	}

	/**
	 * @param $urlEnabledSectionType
	 *
	 * @return array
	 */
	public function getUrlEnabledSectionByType($type)
	{
		foreach ($this->urlEnabledSectionTypes as $urlEnabledSectionType)
		{
			if ($urlEnabledSectionType->getId() == $type)
			{
				return $urlEnabledSectionType;
			}
		}

		return array();
	}

	/**
	 * @return array|\CDbDataReader
	 */
	public function getCustomSections()
	{
		$customSections = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metadata_sections')
			->where('isCustom = 1')
			->queryAll();

		return $customSections;
	}

	/**
	 * Get all Section Metadata from the database.
	 *
	 * @return array
	 */
	public function getAllSectionMetadata()
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
	 * @param $urlEnabledSection
	 *
	 * @return BaseModel|SproutSeo_MetadataModel
	 * @internal param $url
	 *
	 */
	public function getSectionMetadataByInfo($urlEnabledSection)
	{
		$type                          = $urlEnabledSection->type->getElementTableName();
		$urlEnabledSectionIdColumnName = $urlEnabledSection->type->getUrlEnabledSectionIdColumnName();
		$urlEnabledSectionId           = $urlEnabledSection->element->{$urlEnabledSectionIdColumnName};

		$sectionMetadata = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metadata_sections')
			->where('type=:type and urlEnabledSectionId=:urlEnabledSectionId',
				array(':type' => $type, ':urlEnabledSectionId' => $urlEnabledSectionId)
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
			//$model->optimizedTitle = craft()->templates->renderObjectTemplate($model->optimizedTitle, $elementModel);
		}

		return $model;
	}

	/**
	 * @param SproutSeo_MetadataModel $model
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function saveSectionMetadata(SproutSeo_MetadataModel $model)
	{
		if ($model->id)
		{
			if (null === ($record = $this->sectionMetadataRecord->findByPk($model->id)))
			{
				throw new Exception(Craft::t('Can\'t find Section Metadata with ID "{id}"', array(
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

		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;

		if ($record->save())
		{
			// update id on model (for new records)
			$model->id = craft()->db->getLastInsertID();

			if ($transaction !== null)
			{
				$transaction->commit();
			}

			return true;
		}
		else
		{
			if ($transaction !== null)
			{
				$transaction->rollback();
			}

			$model->addErrors($record->getErrors());

			return false;
		}
	}

	/**
	 * @param SproutSeo_MetadataSitemapModel $model
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function saveSectionMetadataViaSitemapSection(SproutSeo_MetadataSitemapModel &$model)
	{
		if ($model->id)
		{
			if (null === ($record = $this->sectionMetadataRecord->findByPk($model->id)))
			{
				throw new Exception(Craft::t('Can\'t find Section Metadata with ID "{id}"', array(
					'id' => $id
				)));
			}
		}
		else
		{
			$record = $this->sectionMetadataRecord->create();
		}

		// Only override the values available to update on the Sitemap page, and the
		// primary values like name and handle if the record needs to be created.
		$record->id                  = $model->id;
		$record->name                = $model->name;
		$record->handle              = $model->handle;
		$record->type                = $model->type;
		$record->urlEnabledSectionId = $model->urlEnabledSectionId;
		$record->url                 = $model->url;
		$record->priority            = $model->priority;
		$record->changeFrequency     = $model->changeFrequency;
		$record->enabled             = $model->enabled;

		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;

		if ($record->save())
		{
			$model->id = $record->id;

			if ($transaction !== null)
			{
				$transaction->commit();
			}

			return true;
		}

		$model->addErrors($record->getErrors());

		if ($transaction !== null)
		{
			$transaction->rollback();
		}

		return false;
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
}
