<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\sectiontypes;

use barrelstrength\sproutseo\base\UrlEnabledSectionType;

use craft\elements\Entry as EntryElement;
use craft\models\Section;
use craft\queue\jobs\ResaveElements;
use Craft;

class Entry extends UrlEnabledSectionType
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
    public function getIdVariableName()
    {
        return 'entryId';
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
     * @return Section|null
     */
    public function getById($id)
    {
        return Craft::$app->sections->getSectionById($id);
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
        return EntryElement::class;
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
        $urlEnabledSections = [];

        $sections = Craft::$app->sections->getAllSections();

        foreach ($sections as $section) {
            $siteSettings = $section->getSiteSettings();

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
        return 'sections_sites';
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

        $section = Craft::$app->sections->getSectionById($elementGroupId);
        $siteSettings = $section->getSiteSettings();

        if ($siteSettings) {
            // let's take the first site
            $primarySite = reset($siteSettings)->siteId ?? null;

            if ($primarySite) {
                Craft::$app->getQueue()->push(new ResaveElements([
                    'description' => Craft::t('sprout-seo', 'Re-saving Entries and metadata'),
                    'elementType' => EntryElement::class,
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
