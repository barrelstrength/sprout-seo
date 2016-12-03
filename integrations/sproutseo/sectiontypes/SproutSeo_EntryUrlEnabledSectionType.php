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

	public function getTableName()
	{
		return 'sections_i18n';
	}

	public function resaveElements()
	{
		// @todo - This data should be available from the SaveFieldLayout event, not relied on in the URL
		$sectionId   = craft()->request->getSegment(3);
		$entryTypeId = craft()->request->getSegment(5);

		$criteria = craft()->elements->getCriteria(ElementType::Entry);

		$locales = array_values(craft()->i18n->getSiteLocaleIds());

		if ($locales)
		{
			foreach ($locales as $locale)
			{
				$criteria->locale        = $locale;
				$criteria->type          = $entryTypeId;
				$criteria->sectionId     = $sectionId;
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
}
