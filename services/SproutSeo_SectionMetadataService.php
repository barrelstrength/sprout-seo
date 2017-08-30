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
		// Have we already prepared our URL-Enabled Sections?
		if (count($this->urlEnabledSectionTypes))
		{
			return null;
		}

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
		$this->prepareUrlEnabledSectionTypes();

		return $this->urlEnabledSectionTypes;
	}

	/**
	 * @param $context
	 *
	 * @return mixed
	 */
	public function getUrlEnabledSectionsViaContext($context)
	{
		$this->prepareUrlEnabledSectionTypes();

		foreach ($this->urlEnabledSectionTypes as $urlEnabledSectionType)
		{
			$urlEnabledSectionType->typeIdContext = 'matchedElementCheck';

			$matchedElementVariable        = $urlEnabledSectionType->getMatchedElementVariable();
			$urlEnabledSectionTypeIdColumn = $urlEnabledSectionType->getIdColumnName();

			// Note: If a template uses a variable with the same name as a potential matched element
			// (getMatchedElementVariable()) on a page where that element doesn't exists Sprout SEO
			// can return the wrong metadata. For example, if an Entry page is loading and someone
			// defines a 'category' variable {% set category = product.category.last() %}, the
			// 'category' variable will be matched before the 'entry' variable.
			if (isset($context[$matchedElementVariable]->{$urlEnabledSectionTypeIdColumn}))
			{
				// Add the current page load matchedElementVariable to our Element Group
				$element = $context[$matchedElementVariable];
				$type    = $urlEnabledSectionType->getId();

				$urlEnabledSectionTypeId = $element->{$urlEnabledSectionTypeIdColumn};

				$uniqueKey = $type . '-' . $urlEnabledSectionTypeId;

				if (isset($urlEnabledSectionType->urlEnabledSections[$uniqueKey]))
				{
					$urlEnabledSection          = $urlEnabledSectionType->urlEnabledSections[$uniqueKey];
					$urlEnabledSection->element = $element;

					return $urlEnabledSection;
				}
			}
		}
	}

	/**
	 * @param $urlEnabledSectionType
	 *
	 * @return array
	 */
	public function getUrlEnabledSectionTypeByType($type)
	{
		$this->prepareUrlEnabledSectionTypes();

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
	 * Get the active URL-Enabled Section Type via the Element Type
	 *
	 * @param $urlEnabledSectionType
	 *
	 * @return array
	 */
	public function getUrlEnabledSectionTypeByElementType($elementType)
	{
		$this->prepareUrlEnabledSectionTypes();

		foreach ($this->urlEnabledSectionTypes as $urlEnabledSectionType)
		{
			if ($urlEnabledSectionType->getElementType() == $elementType)
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
	public function getSectionMetadataByUniqueKey($elementTable, $handle)
	{
		$sectionsRegistered  = sproutSeo()->sectionMetadata->getUrlEnabledSectionTypes();
		$urlEnabledSectionId = null;
		$query = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metadata_sections');

		if ($elementTable == 'sproutseo_section')
		{
			$query->where('handle=:handle', array(':handle' => $handle));
		}
		else if (isset($sectionsRegistered[$elementTable]))
		{
			$sectionType    = $sectionsRegistered[$elementTable];
			$elementSection = null;

			foreach ($sectionType->urlEnabledSections as $uniqueKey => $urlEnabledSection)
			{
				if ($urlEnabledSection->sectionMetadata->handle == $handle)
				{
					$elementSection = $urlEnabledSection;
					break;
				}
			}

			if ($elementSection)
			{
				$urlEnabledSectionId = $elementSection->sectionMetadata->urlEnabledSectionId;
			}

			$query->where('urlEnabledSectionId=:urlEnabledSectionId', array(':urlEnabledSectionId' => $urlEnabledSectionId));

			$query->andWhere('type=:type', array(':type' => $elementTable));
		}

		$results = $query->queryRow();

		if (!isset($results))
		{
			return new SproutSeo_MetadataModel();
		}

		$model = SproutSeo_MetadataModel::populateModel($results);

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
		$type                = $urlEnabledSection->type->getElementTableName();
		$urlEnabledSectionId = sproutSeo()->optimize->urlEnabledSection->id;

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

		if ($model->isCustom)
		{
			$model->setScenario('customSection');

			if (!$model->validate())
			{
				return false;
			}
		}

		// @todo - Refactor
		// is there a better way to do this flip/flop?
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
			$model->id = craft()->db->getLastInsertID();

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

	public function getTransforms()
	{
		$options = array(
			'' => Craft::t('None')
		);

		array_push($options, array('optgroup' => Craft::t('Default Transforms')));

		$options['sproutSeo-socialSquare']     = Craft::t('Square – 400x400');
		$options['sproutSeo-ogRectangle']      = Craft::t('Rectangle – 1200x630 – Open Graph');
		$options['sproutSeo-twitterRectangle'] = Craft::t('Rectangle – 1024x512 – Twitter Card');

		$transforms = craft()->assetTransforms->getAllTransforms();

		if (count($transforms))
		{
			array_push($options, array('optgroup' => Craft::t('Custom Transforms')));

			foreach ($transforms as $transform)
			{
				$options[$transform->handle] = $transform->name;
			}
		}

		return $options;
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
