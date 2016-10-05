<?php
namespace Craft;

/**
 * Class SproutSeo_CategoryUrlEnabledSectionType
 */
class SproutSeo_CategoryUrlEnabledSectionType extends SproutSeoBaseUrlEnabledSectionType
{
	public function getUrlEnabledSectionName()
	{
		return ElementType::Category;
	}

	public function getUrlEnabledSectionIdColumnName()
	{
		return 'groupId';
	}

	public function getUrlEnabledSectionById($id)
	{
		return craft()->categories->getGroupById($id);
	}

	public function getUrlEnabledSectionFieldLayoutSettingsObject($id)
	{
		$group = $this->getUrlEnabledSectionById($id);

		return $group;
	}

	public function getElementTableName()
	{
		return 'categories';
	}

	public function getElementType()
	{
		return ElementType::Entry;
	}

	public function getMatchedElementVariable()
	{
		return 'category';
	}

	public function getAllUrlEnabledSections()
	{
		return craft()->categories->getAllGroups();
	}

	public function getUrlEnabledSectionTableName()
	{
		return 'categorygroups_i18n';
	}
}
