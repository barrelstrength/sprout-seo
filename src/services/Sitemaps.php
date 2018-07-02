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

    public $sitemapSectionRecord;

    /**
     * @var SitemapSectionRecord
     */
    protected $sitemapsRecord;

    public function init()
    {
        $this->sitemapSectionRecord = new SitemapSectionRecord();
    }

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
     * Get all Sitemap from the database.
     *
     * @return array
     */
//    public function getAllSitemapSections()
//    {
//        $results = (new Query())
//            ->select('*')
//            ->from(['{{%sproutseo_sitemaps}}'])
//            ->order('name')
//            ->all();
//
//        $response = [];
//
//        foreach ($results as $key => $section) {
//            array_push($response, new Metadata($section));
//        }
//
//        return $response;
//    }

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
            if (null === ($sectionRecord = SitemapSectionRecord::findOne($sitemapSection->id))) {
                throw new Exception(Craft::t('sprout-seo', 'Unable to find Sitemap with ID "{id}"', [
                    'id' => $sitemapSection->id
                ]));
            }
        } else {
            $sectionRecord = new SitemapSectionRecord();
            $sectionRecord->uniqueKey = $this->generateUniqueKey();
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

        $sectionRecord->id = $sitemapSection->id;
        $sectionRecord->siteId = $sitemapSection->siteId;
        $sectionRecord->urlEnabledSectionId = $sitemapSection->urlEnabledSectionId;
        $sectionRecord->type = $sitemapSection->type;
        $sectionRecord->uri = $sitemapSection->uri;
        $sectionRecord->priority = $sitemapSection->priority;
        $sectionRecord->changeFrequency = $sitemapSection->changeFrequency;
        $sectionRecord->enabled = $sitemapSection->enabled ?? false;

        $transaction = Craft::$app->db->beginTransaction();

        try {
            $sectionRecord->save(false);
            $transaction->commit();
        } catch (\Throwable $e) {
            $sitemapSection->addErrors($sectionRecord->getErrors());
            $transaction->rollBack();
            throw $e;
        }

        // update id on model (for new records)
        $sitemapSection->id = $sectionRecord->id;

        /**
         * @var PluginSettings $pluginSettings
         */
        $pluginSettings = Craft::$app->plugins->getPlugin('sprout-seo')->getSettings();

        // Copy this site behavior to the whole group, for the Url-Enabled Sitemaps
        // Custom Sections will be allowed to be unique, even in Multi-Lingual Sitemaps
        if ($pluginSettings->enableMultilingualSitemaps && $sectionRecord->type !== NoSection::class) {
            $site = Craft::$app->getSites()->getSiteById($sectionRecord->siteId);
            $groupSites = Craft::$app->getSites()->getSitesByGroupId($site->groupId);
            // all sections saved for this site
            $rowsBehavior = SitemapSectionRecord::findAll(['siteId' => $site->id]);

            foreach ($rowsBehavior as $rowBehavior) {
                foreach ($groupSites as $groupSite) {
                    $sitemapSectionRecord = SitemapSectionRecord::findOne([
                        'siteId' => $groupSite->id,
                        'type' => $rowBehavior->type
                    ]);

                    if ($sectionRecord === null) {
                        $sectionRecord = new SitemapSectionRecord();
                    }

                    $sectionRecord->siteId = $groupSite->id;
                    $sectionRecord->type = $rowBehavior->type;
                    $sectionRecord->urlEnabledSectionId = $rowBehavior->urlEnabledSectionId;
                    $sectionRecord->uri = $rowBehavior->uri;
                    $sectionRecord->priority = $rowBehavior->priority;
                    $sectionRecord->changeFrequency = $rowBehavior->changeFrequency;
                    $sectionRecord->enabled = $rowBehavior->enabled;

                    $sectionRecord->save();
                }
            }
        }

        $sitemapSection->id = $sectionRecord->id;

        return true;
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

        // If we don't find a match, we have a unique key
        if (!$result)
        {
            return $key;
        }

        // Try again until we have a unique key
        $this->generateUniqueKey();
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
            $allUrlEnabledSections = $urlEnabledSectionType->getAllUrlEnabledSections();

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
    }

    /**
     * @param $context
     *
     * @return mixed
     * @throws \craft\errors\SiteNotFoundException
     */
    public function getUrlEnabledSectionsViaContext($context)
    {
        $currentSite = Craft::$app->sites->getCurrentSite();

        $this->prepareUrlEnabledSectionTypesForSitemaps($currentSite->id);

        foreach ($this->urlEnabledSectionTypes as $urlEnabledSectionType) {
            $urlEnabledSectionType->typeIdContext = 'matchedElementCheck';

            $matchedElementVariable = $urlEnabledSectionType->getMatchedElementVariable();
            $urlEnabledSectionTypeIdColumn = $urlEnabledSectionType->getIdColumnName();

            if (isset($context[$matchedElementVariable]->{$urlEnabledSectionTypeIdColumn})) {
                // Add the current page load matchedElementVariable to our Element Group
                $element = $context[$matchedElementVariable];
                $type = $urlEnabledSectionType->getId();

                $urlEnabledSectionTypeId = $element->{$urlEnabledSectionTypeIdColumn};

                $uniqueKey = $type.'-'.$urlEnabledSectionTypeId;

                if (isset($urlEnabledSectionType->urlEnabledSections[$uniqueKey])) {
                    $urlEnabledSection = $urlEnabledSectionType->urlEnabledSections[$uniqueKey];
                    $urlEnabledSection->element = $element;

                    return $urlEnabledSection;
                }
            }
        }
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
     * @return UrlEnabledSectionType|array
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

        return [];
    }
}
