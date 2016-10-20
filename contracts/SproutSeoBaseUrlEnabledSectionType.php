<?php
namespace Craft;

/**
 * Class SproutSeoBaseUrlEnabledSectionType
 */
abstract class SproutSeoBaseUrlEnabledSectionType
{
	/**
	 * An array of URL-Enabled Sections for this URL-Enabled Section Type
	 *
	 * @var array SproutSeo_UrlEnabledSectionModel $urlEnabledSections
	 */
	public $urlEnabledSections;

	/**
	 * A silly variable because Craft Commerce inconsistently names productTypeId/typeId.
	 *
	 * Updating this setting allows us to target different typeId column values in different
	 * contexts such as when we are trying to match an element on page load and when we are
	 * trying to determine the URL-format.
	 *
	 * @var
	 */
	public $typeIdContext;

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
	abstract public function getName();

	/**
	 * @param $id
	 *
	 * @return mixed
	 */
	abstract public function getById($id);

	/**
	 * Get the thing that we can call getFieldLayouts on. We will try to loop
	 * through whatever we get back and call getFieldLayouts() on each item in the array.
	 *
	 * @return mixed
	 */
	abstract public function getFieldLayoutSettingsObject($id);

	/**
	 * @return mixed
	 */
	abstract public function getTableName();

	/**
	 * @return mixed
	 */
	abstract public function getIdColumnName();

	/**
	 * By default, we assume the urlFormat setting is in a column of the same name
	 *
	 * @return string
	 */
	public function getUrlFormatColumnName()
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
	 * @return mixed
	 */
	abstract public function getAllUrlEnabledSections();

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
	 * @param $type
	 *
	 * @return array|\CDbDataReader
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

	/**
	 * @param $type
	 *
	 * @return SproutSeo_MetadataModel|null
	 */
	public function getSectionMetadataByTypeAndUrlEnabled($type, $urlEnabledSectionId)
	{
		$result = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_metadata_sections')
			->where('type=:type and urlEnabledSectionId=:urlEnabledSectionId', array(
				':type' => $type,
				':urlEnabledSectionId' => $urlEnabledSectionId
				)
			)
			->queryRow();

		if ($result)
		{
			return SproutSeo_MetadataModel::populateModel($result);
		}

		return $result;
	}
}
