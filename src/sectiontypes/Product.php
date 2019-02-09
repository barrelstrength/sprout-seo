<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\sectiontypes;

use barrelstrength\sproutseo\base\UrlEnabledSectionType;
use barrelstrength\sproutseo\models\UrlEnabledSection;
use craft\commerce\elements\Product as ProductElement;
use Craft;
use craft\commerce\services\ProductTypes;
use craft\queue\jobs\ResaveElements;

/**
 * Class Product
 */
class Product extends UrlEnabledSectionType
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Products';
    }

    /**
     * @return string
     */
    public function getElementIdColumnName(): string
    {
        return 'typeId';
    }

    /**
     * @return string
     */
    public function getUrlFormatIdColumnName(): string
    {
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
    public function getElementTableName(): string
    {
        return 'commerce_products';
    }

    /**
     * @return string
     */
    public function getElementType(): string
    {
        return ProductElement::class;
    }

    /**
     * @return string
     */
    public function getMatchedElementVariable(): string
    {
        return 'product';
    }

    /**
     * @param $siteId
     *
     * @return UrlEnabledSection[]
     */
    public function getAllUrlEnabledSections($siteId): array
    {
        $urlEnabledSections = [];

        $productTypes = new ProductTypes();

        $sections = $productTypes->getAllProductTypes();

        foreach ($sections as $section) {
            $siteSettings = $section->getSiteSettings();

            foreach ($siteSettings as $siteSetting) {
                if ($siteId == $siteSetting->siteId && $siteSetting->hasUrls) {
                    $urlEnabledSections[] = $section;
                }
            }
        }

        return $urlEnabledSections;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return 'commerce_producttypes_sites';
    }

    /**
     * Don't have Sprout SEO trigger ResaveElements task after saving a field layout.
     * This is already supported by Craft Commerce.
     *
     * @return bool
     */
    public function resaveElementsAfterFieldLayoutSaved(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function resaveElements($elementGroupId = null): bool
    {
        if (!$elementGroupId) {
            return false;
        }

        $productTypes = new ProductTypes();
        $productType = $productTypes->getProductTypeById($elementGroupId);
        $siteSettings = array_values($productType->getSiteSettings());

        if (!$siteSettings) {
            return false;
        }
        // let's take the first site
        $primarySite = reset($siteSettings)->siteId ?? null;

        if (!$primarySite) {
            return false;
        }

        Craft::$app->getQueue()->push(new ResaveElements([
            'description' => Craft::t('sprout-seo', 'Re-saving Products and metadata'),
            'elementType' => ProductElement::class,
            'criteria' => [
                'siteId' => $primarySite,
                'typeId' => $elementGroupId,
                'status' => null,
                'enabledForSite' => false,
                'limit' => null,
            ]
        ]));

        return true;
    }
}
