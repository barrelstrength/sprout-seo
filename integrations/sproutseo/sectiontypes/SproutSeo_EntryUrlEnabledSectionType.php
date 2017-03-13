<?php
namespace Craft;

class SproutSeo_EntryUrlEnabledSectionType extends SproutSeoBaseUrlEnabledSectionType
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Sections';
	}

	/**
	 * @return string
	 */
	public function getIdColumnName()
	{
		return 'sectionId';
	}

	/**
	 * @param $id
	 *
	 * @return SectionModel|null
	 */
	public function getById($id)
	{
		return craft()->sections->getSectionById($id);
	}

	/**
	 * @param $id
	 *
	 * @return array
	 */
	public function getFieldLayoutSettingsObject($id)
	{
		$section = $this->getById($id);

		return $section->getEntryTypes();
	}

	/**
	 * @return string
	 */
	public function getElementTableName()
	{
		return 'entries';
	}

	/**
	 * @return string
	 */
	public function getElementType()
	{
		return ElementType::Entry;
	}

	/**
	 * @return string
	 */
	public function getMatchedElementVariable()
	{
		return 'entry';
	}

	/**
	 * @return array
	 */
	public function getAllUrlEnabledSections()
	{
		$urlEnabledSections = array();

		$sections = craft()->sections->getAllSections();

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
		return 'sections_i18n';
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

		$criteria = craft()->elements->getCriteria(ElementType::Entry);

		$section = craft()->sections->getSectionById($elementGroupId);
		$locales = array_values($section->getLocales());

		if ($locales)
		{
			$primaryLocale = $locales[0];

			$criteria->locale        = $primaryLocale->locale;
			$criteria->sectionId     = $elementGroupId;
			$criteria->status        = null;
			$criteria->localeEnabled = null;
			$criteria->limit         = null;

			craft()->tasks->createTask('ResaveElements', Craft::t('Re-saving Entries and metadata'), array(
				'elementType' => ElementType::Entry,
				'criteria'    => $criteria->getAttributes()
			));
		}
	}
}
