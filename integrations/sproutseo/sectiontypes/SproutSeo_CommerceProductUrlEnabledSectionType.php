<?php
namespace Craft;

/**
 * Class SproutSeo_CommerceProductUrlEnabledSectionType
 */
class SproutSeo_CommerceProductUrlEnabledSectionType extends SproutSeoBaseUrlEnabledSectionType
{
	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Commerce Products';
	}

	/**
	 * @return string
	 */
	public function getIdColumnName()
	{
		if ($this->typeIdContext == 'matchedElementCheck')
		{
			return 'typeId';
		}

		return 'productTypeId';
	}

	/**
	 * @param $id
	 *
	 * @return mixed
	 */
	public function getById($id)
	{
		return craft()->commerce_productTypes->getProductTypeById($id);
	}

	/**
	 * @param $id
	 *
	 * @return mixed
	 */
	public function getFieldLayoutSettingsObject($id)
	{
		$productType = $this->getById($id);

		return $productType;
	}

	/**
	 * @return string
	 */
	public function getElementTableName()
	{
		return 'commerce_products';
	}

	/**
	 * @return string
	 */
	public function getElementType()
	{
		return 'Commerce_Product';
	}

	/**
	 * @return string
	 */
	public function getMatchedElementVariable()
	{
		return 'product';
	}

	/**
	 * @return mixed
	 */
	public function getAllUrlEnabledSections()
	{
		$urlEnabledSections = array();

		$sections = craft()->commerce_productTypes->getAllProductTypes();

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
		return 'commerce_producttypes_i18n';
	}

	/**
	 * Don't have Sprout SEO trigger ResaveElements task after saving a field layout.
	 * This is already supported by Craft Commerce.
	 *
	 * @return bool
	 */
	public function resaveElementsAfterFieldLayoutSaved()
	{
		return false;
	}

	/**
	 * @param null $elementGroupId
	 *
	 * @return bool
	 */
	public function resaveElements($elementGroupId = null)
	{
		if (!$elementGroupId)
		{
			// @todo - This data should be available from the SaveFieldLayout event, not relied on in the URL
			$elementGroupId = craft()->request->getSegment(4);
		}

		$criteria = craft()->elements->getCriteria('Commerce_Product');

		$productType = craft()->categories->getGroupById($elementGroupId);
		$locales = array_values($productType->getLocales());

		if ($locales)
		{
			$primaryLocale = $locales[0];

			$criteria->locale        = $primaryLocale->locale;
			$criteria->productTypeId = $elementGroupId;
			$criteria->status        = null;
			$criteria->localeEnabled = null;
			$criteria->limit         = null;

			craft()->tasks->createTask('ResaveElements', Craft::t('Re-saving Commerce Products and metadata.'), array(
				'elementType' => 'Commerce_Product',
				'criteria'    => $criteria->getAttributes()
			));
		}
	}
}
