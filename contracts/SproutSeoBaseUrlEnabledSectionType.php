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
	 * Get a unique ID for this URL-Enabled Section Type
	 *
	 * We use the element table name as the unique ID.
	 *
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
	 * The user-friendly name of your URL-Enabled Section Type
	 *
	 * This name will display in the user interface.
	 *
	 * @return mixed
	 */
	abstract public function getName();

	/**
	 * The variable name where is saved the elementId value
	 * example: entryId, categoryId, productId
	 *
	 * This will help show more easily the live preview
	 *
	 * @return string
	 */
	abstract public function getIdVariableName();

	/**
	 * Allow an integration to define how to get its specific URL-Enabled Section by ID
	 *
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
	 * Return the name of the table that we get URL-Enabled Section info from. In most cases, this is the i18n table.
	 *
	 * @return string
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
	 * Return the name of the Element Type managed by this URL-Enabled Section Type
	 *
	 * @return mixed
	 */
	abstract public function getElementType();

	/**
	 * Return the name of the table that element-specific data is stored
	 *
	 * @return mixed
	 */
	abstract public function getElementTableName();

	/**
	 * Return the variable name that is used by the Element for this URL-Enabled section
	 * when providing the Element data to the page with a URL.
	 *
	 * @example An Entry is made available to a page as `entry`.
	 *          A Category is made available to a page as `category`.
	 *
	 * @return mixed
	 */
	abstract public function getMatchedElementVariable();

	/**
	 * Return all the URL-Enabled Sections for this URL-Enabled Section Type
	 *
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
	 * Get all the URL-Enabled Sections of a particular type that we have stored data for in the Sections section
	 *
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
	 * Add support for triggering the ResaveElements task for this URL-Enabled Section
	 *
	 * @return mixed
	 */
	abstract public function resaveElements();
}
