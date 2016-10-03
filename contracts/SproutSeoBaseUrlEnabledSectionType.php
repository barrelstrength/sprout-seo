<?php
namespace Craft;

/**
 * Class SproutSeoBaseUrlEnabledSectionType
 */
abstract class SproutSeoBaseUrlEnabledSectionType
{
	/**
	 * @var
	 */
	public $urlEnabledSections;

	///**
	// * @var
	// */
	//public $element;

	///**
	// * @var
	// */
	//public $sectionModel;

	/**
	 * @return mixed
	 */
	final public function getId()
	{
		return $this->getElementTableName();
	}

	/**
	 * This setting we'll help us determine if we should use the $locale to limit some queries
	 * like the URL Format query.
	 *
	 * @return bool
	 */
	public function isLocalized()
	{
		return true;
	}

	/**
	 * @return mixed
	 */
	abstract public function getUrlEnabledSectionName();

	/**
	 * @param $id
	 *
	 * @return mixed
	 */
	abstract public function getUrlEnabledSectionById($id);

	/**
	 * @return mixed
	 */
	abstract public function getAllUrlEnabledSections();

	/**
	 * @return mixed
	 */
	abstract public function getUrlEnabledSectionTableName();

	/**
	 * @return mixed
	 */
	abstract public function getUrlEnabledSectionIdColumnName();

	/**
	 * By default, we assume the urlFormat setting is in a column of the same name
	 *
	 * @return string
	 */
	public function getUrlEnabledSectionUrlFormatColumnName()
	{
		return 'urlFormat';
	}

	/**
	 * @return mixed
	 */
	abstract public function getElementType();

	/**
	 * @return mixed
	 */
	abstract public function getElementTableName();

	/**
	 * @return mixed
	 */
	abstract public function getMatchedElementVariable();

	/**
	 * Get all Section Metadata Sections related to this URL-Enabled Section Type
	 *
	 * Order the results by URL-Enabled Section ID: type-id
	 * Example: entries-5, categories-12
	 *
	 * @return mixed
	 */
	public function getAllSectionMetadataSections()
	{
		$type                       = $this->getElementTableName();
		$allSectionMetadataSections = $this->getSectionMetadataByType($type);

		$sectionMetadataSections = array();
		foreach ($allSectionMetadataSections as $sectionMetadataSection)
		{
			$urlEnabledSectionUniqueKey = $this->getId() . '-' . $sectionMetadataSection['urlEnabledSectionId'];

			$sectionMetadataSections[$urlEnabledSectionUniqueKey] = $sectionMetadataSection;
		}

		return $sectionMetadataSections;
	}

	/**
	 * @param $handle
	 *
	 * @return BaseModel|SproutSeo_MetadataModel
	 */
	public function getSectionMetadataByType($type)
	{
		$results = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metadata_sections')
			->where('type=:type', array(':type' => $type))
			->queryAll();

		if ($results)
		{
			return SproutSeo_MetadataModel::populateModels($results);
		}

		return $results;
	}
}
