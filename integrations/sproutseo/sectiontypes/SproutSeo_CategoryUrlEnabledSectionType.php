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

	public function resaveElements($elementGroupId = null)
	{
		if (!$elementGroupId)
		{
			// @todo - This data should be available from the SaveFieldLayout event, not relied on in the URL
			$elementGroupId = craft()->request->getSegment(3);
		}

		$criteria = craft()->elements->getCriteria(ElementType::Category);

		$category = craft()->categories->getGroupById($elementGroupId);
		$locales = array_values($category->getLocales());

		if ($locales)
		{
			$primaryLocale = $locales[0];

			$criteria->locale        = $primaryLocale->locale;
			$criteria->groupId       = $elementGroupId;
			$criteria->status        = null;
			$criteria->localeEnabled = null;
			$criteria->limit         = null;

			craft()->tasks->createTask('ResaveElements', Craft::t('Re-saving Categories and metadata.'), array(
				'elementType' => ElementType::Category,
				'criteria'    => $criteria->getAttributes()
			));
		}
	}
}
