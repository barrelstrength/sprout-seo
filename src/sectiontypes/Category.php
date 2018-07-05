<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\sectiontypes;

use barrelstrength\sproutseo\base\UrlEnabledSectionType;
use barrelstrength\sproutseo\models\UrlEnabledSection;
use craft\elements\Category as CategoryElement;
use craft\models\CategoryGroup;
use craft\queue\jobs\ResaveElements;
use Craft;

class Category extends UrlEnabledSectionType
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Categories';
    }

    /**
     * @return string
     */
    public function getElementIdColumnName()
    {
        return 'groupId';
    }

    /**
     * @return string
     */
    public function getUrlFormatIdColumnName()
    {
        return 'groupId';
    }

    /**
     * @param $id
     *
     * @return CategoryGroup|null
     */
    public function getById($id)
    {
        return Craft::$app->categories->getGroupById($id);
    }

    /**
     * @param $id
     *
     * @return CategoryGroup|null
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
        return 'categories';
    }

    /**
     * @return string
     */
    public function getElementType()
    {
        return CategoryElement::class;
    }

    /**
     * @return string
     */
    public function getMatchedElementVariable()
    {
        return 'category';
    }

    /**
     * @param $siteId
     *
     * @return UrlEnabledSection[]
     */
    public function getAllUrlEnabledSections($siteId)
    {
        $urlEnabledSections = [];

        $sections = Craft::$app->categories->getAllGroups();

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
    public function getTableName()
    {
        return 'categorygroups_sites';
    }

    /**
     * @inheritdoc
     */
    public function resaveElements($elementGroupId = null)
    {
        if (!$elementGroupId) {
            return false;
        }

        $category = Craft::$app->categories->getGroupById($elementGroupId);
        $siteSettings = $category->getSiteSettings();

        if (!$siteSettings) {
            return false;
        }

        // let's take the first site
        $primarySite = reset($siteSettings)->siteId ?? null;

        if (!$primarySite) {
            return false;
        }

        Craft::$app->getQueue()->push(new ResaveElements([
            'description' => Craft::t('sprout-seo', 'Re-saving Categories and metadata.'),
            'elementType' => CategoryElement::class,
            'criteria' => [
                'siteId' => $primarySite,
                'groupId' => $elementGroupId,
                'status' => null,
                'enabledForSite' => false,
                'limit' => null,
            ]
        ]));

        return true;
    }
}
