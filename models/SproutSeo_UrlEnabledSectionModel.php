<?php
namespace Craft;

/**
 * Class SproutSeo_UrlEnabledSectionModel
 */
class SproutSeo_UrlEnabledSectionModel extends BaseModel
{
	/**
	 * URL-Enabled Section ID
	 *
	 * @var
	 */
	public $id;

	/**
	 * @var SproutSeoBaseUrlEnabledSectionType $type
	 */
	public $type;

	/**
	 * @var SproutSeo_MetadataModel $sectionMetadata
	 */
	public $sectionMetadata;

	/**
	 * The current locales URL Format for this URL-Enabled Section
	 *
	 * @var string
	 */
	public $urlFormat;

	/**
	 * The Element Model for the Matched Element Variable of the current page load
	 *
	 * @var BaseElementModel
	 */
	public $element;

	/**
	 * Get the URL format from the element via the Element Group integration
	 *
	 * @param $urlEnabledSection
	 * @param $urlEnabledSectionId
	 *
	 * @return \CDbDataReader|mixed|string
	 */
	public function getUrlFormat()
	{
		$locale = craft()->i18n->getLocaleById(craft()->language);

		$urlEnabledSectionUrlFormatTableName  = $this->type->getUrlEnabledSectionTableName();
		$urlEnabledSectionUrlFormatColumnName = $this->type->getUrlEnabledSectionUrlFormatColumnName();
		$urlEnabledSectionIdColumnName        = $this->type->getUrlEnabledSectionIdColumnName();

		$query = craft()->db->createCommand()
			->select($urlEnabledSectionUrlFormatColumnName)
			->from($urlEnabledSectionUrlFormatTableName)
			->where($urlEnabledSectionIdColumnName . '=:id', array(':id' => $this->id));

		if ($this->type->isLocalized())
		{
			$query->andWhere('locale=:locale', array(':locale' => $locale));
		}

		if ($query->queryScalar())
		{
			$this->urlFormat = $query->queryScalar();
		}

		return $this->urlFormat;
	}

	public function hasEntryMetadataField()
	{
		$fieldLayoutObjects = $this->type->getUrlEnabledSectionFieldLayoutSettingsObject($this->id);

		if (!$fieldLayoutObjects)
		{
			return false;
		}

		// Make what we get back into an array
		if (!is_array($fieldLayoutObjects))
		{
			$fieldLayoutObjects = array($fieldLayoutObjects);
		}

		$totalFieldLayouts      = count($fieldLayoutObjects);
		$totalElementMetaFields = 0;

		// We want to make sure there is an Entry Metadata field on every field layout object.
		// For example, a Category Group or Product Type just needs one Entry Metadata for its Field Layout.
		// A section with multiple Entry Types needs an Entry Metadata field on each of it's Field Layouts.
		foreach ($fieldLayoutObjects as $fieldLayoutObject)
		{
			$fields = $fieldLayoutObject->getFieldLayout()->getFields();

			foreach ($fields as $fieldLayoutField)
			{
				if ($fieldLayoutField->getField()->type == 'SproutSeo_EntryMetadata')
				{
					$totalElementMetaFields++;
				}
			}
		}

		// If we have an equal number of Entry Metadata fields,
		// the setup is optimized to handle metadata at each level
		if ($totalElementMetaFields >= $totalFieldLayouts)
		{
			return true;
		}

		return false;
	}
}
