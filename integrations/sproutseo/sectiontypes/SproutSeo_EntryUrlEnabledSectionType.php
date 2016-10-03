<?php
namespace Craft;

/**
 * Class SproutSeo_EntryUrlEnabledSectionType
 */
class SproutSeo_EntryUrlEnabledSectionType extends SproutSeoBaseUrlEnabledSectionType
{
	public function getUrlEnabledSectionName()
	{
		return 'Sections';
	}

	public function getUrlEnabledSectionIdColumnName()
	{
		return 'sectionId';
	}

	public function getUrlEnabledSectionById($id)
	{
		return craft()->sections->getSectionById($id);
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

	public function getUrlEnabledSectionTableName()
	{
		return 'sections_i18n';
	}
}
