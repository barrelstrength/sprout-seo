<?php
namespace Craft;

class SproutSeo_CategoryUrlEnabledSectionType extends SproutSeoBaseUrlEnabledSectionType
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return ElementType::Category;
	}

	/**
	 * @return string
	 */
	public function getIdVariableName()
	{
		return 'categoryId';
	}

	/**
	 * @return string
	 */
	public function getIdColumnName()
	{
		return 'groupId';
	}

	/**
	 * @param $id
	 *
	 * @return CategoryGroupModel|null
	 */
	public function getById($id)
	{
		return craft()->categories->getGroupById($id);
	}

	/**
	 * @param $id
	 *
	 * @return CategoryGroupModel|null
	 */
	public function getFieldLayoutSettingsObject($id)
	{
		$group = $this->getById($id);

		return $group;
	}

	/**
	 * @return string
	 */
	public function getElementTableName()
	{
		return 'categories';
	}

	/**
	 * @return string
	 */
	public function getElementType()
	{
		return ElementType::Category;
	}

	/**
	 * @return string
	 */
	public function getMatchedElementVariable()
	{
		return 'category';
	}

	/**
	 * @return array
	 */
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

	/**
	 * @return string
	 */
	public function getTableName()
	{
		return 'categorygroups_i18n';
	}

	/**
	 * @param null $elementGroupId
	 */
	public function resaveElements($elementGroupId = null)
	{
		if (!$elementGroupId)
		{
			// @todo - Craft Feature Request
			// This data should be available from the SaveFieldLayout event, not relied on in the URL
			$elementGroupId = craft()->request->getSegment(3);
		}

		$criteria = craft()->elements->getCriteria(ElementType::Category);

		$category = craft()->categories->getGroupById($elementGroupId);
		$locales  = array_values($category->getLocales());

		if ($locales)
		{
			foreach ($locales as $key => $locale)
			{
				$criteria->locale        = $locale->locale;
				$criteria->groupId       = $elementGroupId;
				$criteria->status        = null;
				$criteria->localeEnabled = null;
				$criteria->limit         = null;

				craft()->tasks->createTask('ResaveElements', Craft::t('Re-saving Categories: '.$category->name. ' language. '.$locale->locale), array(
					'elementType' => ElementType::Category,
					'criteria'    => $criteria->getAttributes()
				));
			}
		}
	}
}
