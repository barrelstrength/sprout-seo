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
	public function getIdVariableName()
	{
		return 'productId';
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
	 * @param null $elementGroupId
	 *
	 * @return bool
	 */
	public function resaveElements($elementGroupId = null)
	{
		if (!$elementGroupId)
		{
			// @todo - Craft Feature Request
			// This data should be available from the SaveFieldLayout event, not relied on in the URL
			$elementGroupId = craft()->request->getSegment(4);
		}

		$criteria = craft()->elements->getCriteria('Commerce_Product');

		$productType = craft()->commerce_productTypes->getProductTypeById($elementGroupId);
		$locales = array_values($productType->getLocales());

		if ($locales)
		{
			foreach ($locales as $key => $locale)
			{
				$criteria->locale        = $locale->locale;
				$criteria->productTypeId = $elementGroupId;
				$criteria->status        = null;
				$criteria->localeEnabled = null;
				$criteria->limit         = null;

				craft()->tasks->createTask('ResaveElements', Craft::t('Re-saving Commerce Product'.$productType->name.' language: '.$locale->locale), array(
					'elementType' => 'Commerce_Product',
					'criteria'    => $criteria->getAttributes()
				));
			}
		}
	}
}
