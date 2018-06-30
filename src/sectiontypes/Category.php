<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\sectiontypes;

use barrelstrength\sproutseo\base\UrlEnabledSectionType;

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
     * @return array
     */
    public function getAllUrlEnabledSections()
    {
        $urlEnabledSections = [];

        $sections = Craft::$app->categories->getAllGroups();
        foreach ($sections as $section) {
            $siteSettings = $section->getSiteSettings();
            // @todo - at least one site with url ?
            foreach ($siteSettings as $siteSetting) {
                if ($siteSetting->hasUrls) {
                    $urlEnabledSections[] = $section;
                    break;
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
     * @param int|string|null $elementGroupId
     */
    public function resaveElements($elementGroupId = null)
    {
        if (!$elementGroupId) {
            // @todo - Craft Feature Request
            // This data should be available from the SaveFieldLayout event, not relied on in the URL
            $elementGroupId = Craft::$app->request->getSegment(3);
        }

        $category = Craft::$app->categories->getGroupById($elementGroupId);
        $siteSettings = $category->getSiteSettings();

        if ($siteSettings) {
            // let's take the first site
            $primarySite = reset($siteSettings)->siteId ?? null;

            if ($primarySite) {
                Craft::$app->getQueue()->push(new ResaveElements([
                    'description' => Craft::t('sprout-seo', 'Re-saving Categories and metadata.'),
                    'elementType' => CategoryElement::class,
                    'criteria' => [
                        'siteId' => $primarySite,
                        'sectionId' => $elementGroupId,
                        'status' => null,
                        'enabledForSite' => false,
                        'limit' => null,
                    ]
                ]));
            }
        }
    }
}
