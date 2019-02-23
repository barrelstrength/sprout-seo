<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\sectiontypes;

use barrelstrength\sproutseo\base\UrlEnabledSectionType;
use barrelstrength\sproutseo\models\UrlEnabledSection;
use craft\elements\Entry as EntryElement;
use craft\models\Section;
use craft\queue\jobs\ResaveElements;
use Craft;

class Entry extends UrlEnabledSectionType
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Sections';
    }

    /**
     * @return string
     */
    public function getElementIdColumnName(): string
    {
        return 'sectionId';
    }

    /**
     * @return string
     */
    public function getUrlFormatIdColumnName(): string
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
    public function getElementTableName(): string
    {
        return 'entries';
    }

    /**
     * @return string
     */
    public function getElementType(): string
    {
        return EntryElement::class;
    }

    /**
     * @inheritdoc
     */
    public function getElementLiveStatus()
    {
        return EntryElement::STATUS_LIVE;
    }

    /**
     * @return string
     */
    public function getMatchedElementVariable(): string
    {
        return 'entry';
    }

    /**
     * @param $siteId
     *
     * @return UrlEnabledSection[]
     */
    public function getAllUrlEnabledSections($siteId): array
    {
        $urlEnabledSections = [];

        $sections = Craft::$app->sections->getAllSections();

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
        return 'sections_sites';
    }

    /**
     * @inheritdoc
     */
    public function resaveElements($elementGroupId = null): bool
    {
        if (!$elementGroupId) {
            return false;
        }

        $section = Craft::$app->sections->getSectionById($elementGroupId);
        $siteSettings = $section->getSiteSettings();

        if (!$siteSettings) {
            return false;
        }
        // let's take the first site
        $primarySite = reset($siteSettings)->siteId ?? null;

        if (!$primarySite) {
            return false;
        }

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

        return true;
    }
}
