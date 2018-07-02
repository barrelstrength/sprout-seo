<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\sectiontypes;

use barrelstrength\sproutseo\base\UrlEnabledSectionType;
use craft\commerce\elements\Product as ProductElement;
use Craft;
use craft\commerce\services\ProductTypes;

/**
 * Class Product
 */
class Product extends UrlEnabledSectionType
{
    /**
     * @return string
     */
    public function getName()
    {
        return ProductElement::class;
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
        if ($this->typeIdContext == 'matchedElementCheck') {
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
        $productTypes = new ProductTypes();

        return $productTypes->getProductTypeById($id);
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function getFieldLayoutSettingsObject($id)
    {
        return $this->getById($id);
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
        return ProductElement::class;
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
        $urlEnabledSections = [];

        $productTypes = new ProductTypes();

        $sections = $productTypes->getAllProductTypes();

        foreach ($sections as $section) {
            if ($section->hasUrls) {
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
        return 'commerce_producttypes_sites';
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
     * @return mixed|void
     */
    public function resaveElements($elementGroupId = null)
    {
        if (!$elementGroupId) {
            // @todo - Craft Feature Request
            // This data should be available from the SaveFieldLayout event, not relied on in the URL
            $elementGroupId = Craft::$app->request->getSegment(4);
        }

        $productType = Craft::$app->categories->getGroupById($elementGroupId);
        $siteSettings = array_values($productType->getSiteSettings());

        if ($siteSettings) {

//            $primaryLocale = $siteSettings[0];

//            $query = ProductElement::find();
//            $query->siteId = $primaryLocale->locale;
//            $query->productTypeId = $elementGroupId;
//            $query->status = null;
//            $query->localeEnabled = null;
//            $query->limit = null;
//
//            Craft::$app->tasks->createTask('ResaveElements', Craft::t('sprout-seo', 'Re-saving Commerce Products and metadata.'), [
//                'elementType' => Product::class,
//                'criteria' => $criteria->getAttributes()
//            ]);
        }
    }
}
