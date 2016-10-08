<?php
namespace Craft;

/**
 * Class SproutSeo_EntryUrlEnabledSectionType
 */
class SproutSeo_EntryUrlEnabledSectionType extends SproutSeoBaseUrlEnabledSectionType
{
	public function getName()
	{
		return 'Sections';
	}

	public function getIdColumnName()
	{
		return 'sectionId';
	}

	public function getById($id)
	{
		return craft()->sections->getSectionById($id);
	}

	public function getFieldLayoutSettingsObject($id)
	{
		$section = $this->getById($id);

		return $section->getEntryTypes();
	}

	public function getElementTableName()
	{
		return 'entries';
	}

	public function getElementType()
	{
		return ElementType::Entry;
	}

	public function getMatchedElementVariable()
	{
		return 'entry';
	}

	public function getAllUrlEnabledSections()
	{
		return craft()->sections->getAllSections();
	}

	public function getTableName()
	{
		return 'sections_i18n';
	}
}
