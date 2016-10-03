<?php
namespace Craft;

/**
 * Class SproutSeo_CommerceProductUrlEnabledSectionType
 */
class SproutSeo_CommerceProductUrlEnabledSectionType extends SproutSeoBaseUrlEnabledSectionType
{
	public function getUrlEnabledSectionName()
	{
		return 'Commerce Products';
	}

	public function getUrlEnabledSectionIdColumnName()
	{
		return 'typeId';
	}

	public function getUrlEnabledSectionById($id)
	{
		// @todo
		return null;
	}

	public function getElementTableName()
	{
		return 'commerce_products';
	}

	public function getElementType()
	{
		return 'Commerce_Product';
	}

	public function getMatchedElementVariable()
	{
		return 'product';
	}

	public function getAllUrlEnabledSections()
	{
		return craft()->commerce_productTypes->getAllProductTypes();
	}

	public function getUrlEnabledSectionTableName()
	{
		return 'producttypes_i18n';
	}
}