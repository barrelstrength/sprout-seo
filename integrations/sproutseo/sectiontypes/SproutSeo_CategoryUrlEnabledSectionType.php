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
		return ElementType::Category;
	}

	public function getMatchedElementVariable()
	{
		return 'category';
	}

	public function getAllUrlEnabledSections()
	{
		$urlEnabledSections = array();

		$sections = craft()->categories->getAllGroups();

		foreach ($sections as $section) 
		{
			if ($section->hasUrls)
			{
				$urlEnabledSections[] = $section;
			}
		}

		return $urlEnabledSections;
	}

	public function getTableName()
	{
		return 'categorygroups_i18n';
	}
}
