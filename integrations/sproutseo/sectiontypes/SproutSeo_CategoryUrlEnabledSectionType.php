<?php
namespace Craft;

/**
 * Class SproutSeo_CategoryUrlEnabledSectionType
 */
class SproutSeo_CategoryUrlEnabledSectionType extends SproutSeoBaseUrlEnabledSectionType
{
	public function getName()
	{
		return ElementType::Category;
	}

	public function getIdColumnName()
	{
		return 'groupId';
	}

	public function getById($id)
	{
		return craft()->categories->getGroupById($id);
	}

	public function getFieldLayoutSettingsObject($id)
	{
		$group = $this->getById($id);

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

	public function getTableName()
	{
		return 'categorygroups_i18n';
	}
}
