<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\services;

use barrelstrength\sproutseo\base\UrlEnabledSectionType;
use barrelstrength\sproutseo\models\SitemapSection;
use barrelstrength\sproutseo\models\UrlEnabledSection;
use barrelstrength\sproutseo\sectiontypes\NoSection;
use barrelstrength\sproutseo\SproutSeo;
use barrelstrength\sproutseo\models\Settings as PluginSettings;
use craft\base\Element;
use craft\errors\SiteNotFoundException;
use yii\base\Component;
use craft\db\Query;
use Craft;
use barrelstrength\sproutseo\records\SitemapSection as SitemapSectionRecord;
use yii\db\Exception;


class Sitemaps extends Component
{
    /**
     * @var array
     */
    public $urlEnabledSectionTypes;

    /**
     * @var SitemapSectionRecord
     */
    protected $sitemapsRecord;

    /**
     * Returns all Custom Sitemap Sections
     *
     * @param $siteId
     *
     * @return array
     */
    public function getCustomSitemapSections($siteId)
    {
        $customSections = (new Query())
            ->select('*')
            ->from(['{{%sproutseo_sitemaps}}'])
            ->where('siteId=:siteId', [':siteId' => $siteId])
            ->andWhere('type=:type', [':type' => NoSection::class])
            ->all();

        $sitemapSections = [];

        if ($customSections) {
            foreach ($customSections as $customSection) {
                $sitemapSections[] = new SitemapSection($customSection);
            }
        }

        return $sitemapSections;
    }

    /**
     * Get all Sitemap Sections related to this URL-Enabled Section Type
     *
     * Order the results by URL-Enabled Section ID: type-id
     * Example: entries-5, categories-12
     *
     * @param UrlEnabledSectionType $urlEnabledSectionType
     * @param null                  $siteId
     *
     * @return array
     * @throws \craft\errors\SiteNotFoundException
     */
    public function getSitemapSections(UrlEnabledSectionType $urlEnabledSectionType, $siteId = null)
    {
        $type = get_class($urlEnabledSectionType);
        $allSitemapSections = SproutSeo::$app->sitemaps->getSitemapSectionsByType($type, $siteId);

        $sitemapSections = [];

        foreach ($allSitemapSections as $sitemapSection) {
            $urlEnabledSectionUniqueKey = $urlEnabledSectionType->getId().'-'.$sitemapSection['urlEnabledSectionId'];

            $sitemapSections[$urlEnabledSectionUniqueKey] = $sitemapSection;
        }

        return $sitemapSections;
    }

    /**
     * Get all the Sitemap Sections of a particular type
     *
     * @param $type
     *
     * @param $siteId
     *
     * @return array
     * @throws \craft\errors\SiteNotFoundException
     */
    public function getSitemapSectionsByType($type, $siteId = null)
    {
        if ($siteId === null) {
            throw new SiteNotFoundException('Unable to find site. $siteId must not be null');
        }

        $results = (new Query())
            ->select('*')
            ->from(['{{%sproutseo_sitemaps}}'])
            ->where(['type' => $type, 'siteId' => $siteId])
            ->all();

        $sitemapSections = [];

        if ($results) {
            foreach ($results as $result) {
                $sitemapSections[] = new SitemapSection($result);
            }
        }

        return $sitemapSections;
    }

    /**
     * Returns a Sitemap Section by ID
     *
     * @param $id
     *
     * @return SitemapSection|null
     */
    public function getSitemapSectionById($id)
    {
        $result = (new Query())
            ->select('*')
            ->from(['{{%sproutseo_sitemaps}}'])
            ->where(['id' => $id])
            ->one();

        if ($result) {
            return new SitemapSection($result);
        }

        return null;
    }

    /**
     * @param SitemapSection $sitemapSection
     *
     * @return bool
     * @throws Exception
     * @throws \Throwable
     */
    public function saveSitemapSection(SitemapSection $sitemapSection): bool
    {
        $isNewSection = !$sitemapSection->id;

        if (!$isNewSection) {
            if (null === ($sitemapSectionRecord = SitemapSectionRecord::findOne($sitemapSection->id))) {
                throw new Exception(Craft::t('sprout-seo', 'Unable to find Sitemap with ID "{id}"', [
                    'id' => $sitemapSection->id
                ]));
            }
        } else {
            $sitemapSectionRecord = new SitemapSectionRecord();
            $sitemapSectionRecord->uniqueKey = $this->generateUniqueKey();
        }

        if ($sitemapSection->type === NoSection::class) {
            $sitemapSection->setScenario('customSection');

            if (!$sitemapSection->validate()) {
                return false;
            }
        }

        $sitemapSection->validate();

        if ($sitemapSection->getErrors()) {
            return false;
        }

        $sitemapSectionRecord->id = $sitemapSection->id;
        $sitemapSectionRecord->siteId = $sitemapSection->siteId;
        $sitemapSectionRecord->urlEnabledSectionId = $sitemapSection->urlEnabledSectionId;
        $sitemapSectionRecord->type = $sitemapSection->type;
        $sitemapSectionRecord->uri = $sitemapSection->uri;
        $sitemapSectionRecord->priority = $sitemapSection->priority;
        $sitemapSectionRecord->changeFrequency = $sitemapSection->changeFrequency;
        $sitemapSectionRecord->enabled = $sitemapSection->enabled ?? false;

        $transaction = Craft::$app->db->beginTransaction();

        try {
            $sitemapSectionRecord->save(false);
            $transaction->commit();
        } catch (\Throwable $e) {
            $sitemapSection->addErrors($sitemapSectionRecord->getErrors());
            $transaction->rollBack();
            throw $e;
        }

        // update id on model (for new records)
        $sitemapSection->id = $sitemapSectionRecord->id;

        /**
         * @var PluginSettings $pluginSettings
         */
        $pluginSettings = Craft::$app->plugins->getPlugin('sprout-seo')->getSettings();

        // Copy this site behavior to the whole group, for the Url-Enabled Sitemaps
        // Custom Sections will be allowed to be unique, even in Multi-Lingual Sitemaps
        if ($pluginSettings->enableMultilingualSitemaps && $sitemapSectionRecord->type !== NoSection::class) {
            $site = Craft::$app->getSites()->getSiteById($sitemapSectionRecord->siteId);
            $sitesInGroup = Craft::$app->getSites()->getSitesByGroupId($site->groupId);

            $siteIds = [];
            foreach ($sitesInGroup as $siteInGroup) {
                $siteIds[] = $siteInGroup->id;
            }

            // all sections saved for this site
            $sitemapSectionRecords = SitemapSectionRecord::find()
                ->where(['in', 'siteId', $siteIds])
                ->andWhere('urlEnabledSectionId= :urlEnabledSectionId', [
                    ':urlEnabledSectionId' => $sitemapSectionRecord->urlEnabledSectionId
                ])
                ->indexBy('siteId')
                ->all();

            foreach ($sitesInGroup as $siteInGroup) {

                if (isset($sitemapSectionRecords[$siteInGroup->id])) {
                    $sitemapSectionRecord = $sitemapSectionRecords[$siteInGroup->id];
                } else {
                    $sitemapSectionRecord = new SitemapSectionRecord();
                    $sitemapSectionRecord->uniqueKey = $this->generateUniqueKey();
                }

                $sitemapSectionRecord->siteId = $siteInGroup->id;
                $sitemapSectionRecord->type = $sitemapSection->type;
                $sitemapSectionRecord->urlEnabledSectionId = $sitemapSection->urlEnabledSectionId;
                $sitemapSectionRecord->uri = $sitemapSection->uri;
                $sitemapSectionRecord->priority = $sitemapSection->priority;
                $sitemapSectionRecord->changeFrequency = $sitemapSection->changeFrequency;
                $sitemapSectionRecord->enabled = $sitemapSection->enabled;

                $sitemapSectionRecord->save();
            }
        }

        $sitemapSection->id = $sitemapSectionRecord->id;

        return true;
    }

    /**
     * Delete a Sitemap by ID
     *
     * @param null $id
     *
     * @return bool
     * @throws Exception
     */
    public function deleteSitemapSectionById($id = null)
    {
        $sitemapSectionRecord = SitemapSectionRecord::findOne($id);

        if (!$sitemapSectionRecord) {
            return false;
        }

        $affectedRows = Craft::$app->getDb()->createCommand()
            ->delete('{{%sproutseo_sitemaps}}', [
                'id' => $id
            ])
            ->execute();

        return (bool)$affectedRows;
    }

    /**
     * @return string
     * @throws \yii\base\Exception
     */
    public function generateUniqueKey()
    {
        $key = Craft::$app->getSecurity()->generateRandomString(12);

        $result = (new Query())
            ->select('uniqueKey')
            ->from(['{{%sproutseo_sitemaps}}'])
            ->where(['uniqueKey' => $key])
            ->scalar();

        if ($result) {
            // Try again until we have a unique key
            $this->generateUniqueKey();
        }

        return $key;
    }

    public function getTransforms()
    {
        $options = [
            '' => Craft::t('sprout-seo', 'None')
        ];

        $options[] = ['optgroup' => Craft::t('sprout-seo', 'Default Transforms')];

        $options['sproutSeo-socialSquare'] = Craft::t('sprout-seo', 'Square – 400x400');
        $options['sproutSeo-ogRectangle'] = Craft::t('sprout-seo', 'Rectangle – 1200x630 – Open Graph');
        $options['sproutSeo-twitterRectangle'] = Craft::t('sprout-seo', 'Rectangle – 1024x512 – Twitter Card');

        $transforms = Craft::$app->assetTransforms->getAllTransforms();

        if (count($transforms)) {
            $options[] = ['optgroup' => Craft::t('sprout-seo', 'Custom Transforms')];

            foreach ($transforms as $transform) {
                $options[$transform->handle] = $transform->name;
            }
        }

        return $options;
    }

    /**
     * Get all registered Element Groups
     *
     * @param null $siteId
     *
     * @return UrlEnabledSectionType[]
     * @throws \craft\errors\SiteNotFoundException
     */
    public function getUrlEnabledSectionTypesForSitemaps($siteId = null)
    {
        $this->prepareUrlEnabledSectionTypesForSitemaps($siteId);

        return $this->urlEnabledSectionTypes;
    }

    /**
     * Prepare the $this->urlEnabledSectionTypes variable for use in Sections and Sitemap pages
     *
     * @param null $siteId
     *
     * @return null
     * @throws \craft\errors\SiteNotFoundException
     */
    public function prepareUrlEnabledSectionTypesForSitemaps($siteId = null)
    {
        // Have we already prepared our URL-Enabled Sections?
        if (!empty($this->urlEnabledSectionTypes)) {
            return null;
        }

        $registeredUrlEnabledSectionsTypes = SproutSeo::$app->urlEnabledSections->getRegisteredUrlEnabledSectionsEvent();

        foreach ($registeredUrlEnabledSectionsTypes as $urlEnabledSectionType) {
            /**
             * @var UrlEnabledSectionType $urlEnabledSectionType
             */
            $urlEnabledSectionType = new $urlEnabledSectionType();
            $sitemapSections = SproutSeo::$app->sitemaps->getSitemapSections($urlEnabledSectionType, $siteId);
            $allUrlEnabledSections = $urlEnabledSectionType->getAllUrlEnabledSections($siteId);

            // Prepare a list of all URL-Enabled Sections for this URL-Enabled Section Type
            // if we have an existing Sitemap, use it, otherwise fallback to a new model
            $urlEnabledSections = [];

            /**
             * @var UrlEnabledSection $urlEnabledSection
             */
            foreach ($allUrlEnabledSections as $urlEnabledSection) {
                $uniqueKey = $urlEnabledSectionType->getId().'-'.$urlEnabledSection->id;

                $model = new UrlEnabledSection();
                $sitemapSection = null;

                if (isset($sitemapSections[$uniqueKey])) {
                    // If an URL-Enabled Section exists as Sitemap, use it
                    $sitemapSection = $sitemapSections[$uniqueKey];
                    $sitemapSection->id = $sitemapSections[$uniqueKey]->id;
                } else {
                    // If no URL-Enabled Section exists, create a new one
                    $sitemapSection = new SitemapSection();
                    $sitemapSection->isNew = true;
                    $sitemapSection->urlEnabledSectionId = $urlEnabledSection->id;
                }

                $model->type = $urlEnabledSectionType;
                $model->id = $urlEnabledSection->id;

                $sitemapSection->name = $urlEnabledSection->name;
                $sitemapSection->handle = $urlEnabledSection->handle;
                $sitemapSection->uri = $model->getUrlFormat();

                $model->sitemapSection = $sitemapSection;

                $urlEnabledSections[$uniqueKey] = $model;
            }

            $urlEnabledSectionType->urlEnabledSections = $urlEnabledSections;

            $this->urlEnabledSectionTypes[$urlEnabledSectionType->getId()] = $urlEnabledSectionType;
        }

        return null;
    }

    /**
     * @param $context
     *
     * @return Element|null
     * @throws SiteNotFoundException
     */
    public function getElementViaContext($context)
    {
        $currentSite = Craft::$app->sites->getCurrentSite();

        $this->prepareUrlEnabledSectionTypesForSitemaps($currentSite->id);

        foreach ($this->urlEnabledSectionTypes as $urlEnabledSectionType) {
            $matchedElementVariable = $urlEnabledSectionType->getMatchedElementVariable();

            if (isset($context[$matchedElementVariable])) {
                return $context[$matchedElementVariable];
            }
        }

        return null;
    }

    /**
     * @param $type
     *
     * @return UrlEnabledSectionType|array
     * @throws SiteNotFoundException
     */
    public function getUrlEnabledSectionTypeByType($type)
    {
        $currentSite = Craft::$app->sites->getCurrentSite();

        $this->prepareUrlEnabledSectionTypesForSitemaps($currentSite->id);

        foreach ($this->urlEnabledSectionTypes as $urlEnabledSectionType) {
            if (get_class($urlEnabledSectionType) == $type) {
                return $urlEnabledSectionType;
            }
        }

        return [];
    }

    /**
     * Get the active URL-Enabled Section Type via the Element Type
     *
     * @param $elementType
     *
     * @return UrlEnabledSectionType|null
     * @throws \craft\errors\SiteNotFoundException
     */
    public function getUrlEnabledSectionTypeByElementType($elementType)
    {
        $currentSite = Craft::$app->sites->getCurrentSite();

        $this->prepareUrlEnabledSectionTypesForSitemaps($currentSite->id);

        foreach ($this->urlEnabledSectionTypes as $urlEnabledSectionType) {
            if ($urlEnabledSectionType->getElementType() == $elementType) {
                return $urlEnabledSectionType;
            }
        }

        return null;
    }
}
