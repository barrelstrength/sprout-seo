<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\services;


use barrelstrength\sproutseo\events\RegisterUrlEnabledSectionTypesEvent;
use barrelstrength\sproutseo\helpers\SproutSeoOptimizeHelper;
use barrelstrength\sproutseo\models\Metadata;
use barrelstrength\sproutseo\models\MetadataSitemap;
use barrelstrength\sproutseo\models\UrlEnabledSection;
use barrelstrength\sproutseo\SproutSeo;
use yii\base\Component;
use craft\db\Query;
use Craft;
use barrelstrength\sproutseo\records\SectionMetadata as SectionMetadataRecord;


class SectionMetadata extends Component
{
    const EVENT_REGISTER_URL_ENABLED_SECTION_TYPES = 'registerUrlEnabledSectionTypesEvent';

    /**
     * @var
     */
    public $urlEnabledSectionTypes;

    /**
     * @var SectionMetadataRecord|object
     */
    protected $sectionMetadataRecord;

    public function init()
    {
        $this->sectionMetadataRecord = new SectionMetadataRecord();
    }

    /**
     * Prepare the $this->urlEnabledSectionTypes variable for use in Sections and Sitemap pages
     *
     * @param null $siteId
     *
     * @return null
     * @throws \craft\errors\SiteNotFoundException
     */
    public function prepareUrlEnabledSectionTypes($siteId = null)
    {
        // Have we already prepared our URL-Enabled Sections?
        if (count($this->urlEnabledSectionTypes)) {
            return null;
        }

        $registeredUrlEnabledSectionsTypes = $this->getRegisteredUrlEnabledSectionsEvent();

        foreach ($registeredUrlEnabledSectionsTypes as $urlEnabledSectionType) {
            $sectionMetadataSections = $urlEnabledSectionType->getAllSectionMetadataSections($siteId);
            $allUrlEnabledSections = $urlEnabledSectionType->getAllUrlEnabledSections();

            // Prepare a list of all URL-Enabled Sections for this URL-Enabled Section Type
            // if we have an existing Section Metadata, use it, otherwise fallback to a new model
            $urlEnabledSections = [];

            /**
             * @var UrlEnabledSection $urlEnabledSection
             */
            foreach ($allUrlEnabledSections as $urlEnabledSection) {
                $uniqueKey = $urlEnabledSectionType->getId().'-'.$urlEnabledSection->id;

                $model = new UrlEnabledSection();
                $sectionMetadata = null;

                if (isset($sectionMetadataSections[$uniqueKey])) {
                    // If an URL-Enabled Section exists as section metadata, use it
                    $sectionMetadata = $sectionMetadataSections[$uniqueKey];
                    $sectionMetadata->id = $sectionMetadataSections[$uniqueKey]->id;
                } else {
                    // If no URL-Enabled Section exists, create a new one
                    $sectionMetadata = new Metadata();
                    $sectionMetadata->isNew = true;
                    $sectionMetadata->urlEnabledSectionId = $urlEnabledSection->id;
                }

                $model->type = $urlEnabledSectionType;
                $model->id = $urlEnabledSection->id;

                $sectionMetadata->name = $urlEnabledSection->name;
                $sectionMetadata->handle = $urlEnabledSection->handle;
                $sectionMetadata->uri = $model->getUrlFormat();

                $model->sectionMetadata = $sectionMetadata;

                $urlEnabledSections[$uniqueKey] = $model;
            }

            $urlEnabledSectionType->urlEnabledSections = $urlEnabledSections;

            $this->urlEnabledSectionTypes[$urlEnabledSectionType->getId()] = $urlEnabledSectionType;
        }
    }

    /**
     * Get all registered Element Groups via hook
     *
     * @param null $siteId
     *
     * @return mixed
     * @throws \craft\errors\SiteNotFoundException
     */
    public function getUrlEnabledSectionTypes($siteId = null)
    {
        $this->prepareUrlEnabledSectionTypes($siteId);

        return $this->urlEnabledSectionTypes;
    }

    /**
     * @param $context
     *
     * @return mixed
     * @throws \craft\errors\SiteNotFoundException
     */
    public function getUrlEnabledSectionsViaContext($context)
    {
        $this->prepareUrlEnabledSectionTypes();

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
     * @return array
     * @throws \craft\errors\SiteNotFoundException
     */
    public function getUrlEnabledSectionTypeByType($type)
    {
        $this->prepareUrlEnabledSectionTypes();

        foreach ($this->urlEnabledSectionTypes as $urlEnabledSectionType) {
            if ($urlEnabledSectionType->getId() == $type) {
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
     * @return array
     * @throws \craft\errors\SiteNotFoundException
     */
    public function getUrlEnabledSectionTypeByElementType($elementType)
    {
        $this->prepareUrlEnabledSectionTypes();

        foreach ($this->urlEnabledSectionTypes as $urlEnabledSectionType) {
            if ($urlEnabledSectionType->getElementType() == $elementType) {
                return $urlEnabledSectionType;
            }
        }

        return [];
    }

    /**
     * @return array
     */
    public function getCustomSections()
    {
        $customSections = (new Query())
            ->select('*')
            ->from(['{{%sproutseo_sitemaps}}'])
            ->where('isCustom = 1')
            ->all();

        return $customSections;
    }

    /**
     * Get all Section Metadata from the database.
     *
     * @return array
     */
    public function getAllSectionMetadata()
    {
        $results = (new Query())
            ->select('*')
            ->from(['{{%sproutseo_sitemaps}}'])
            ->order('name')
            ->all();

        $response = [];

        foreach ($results as $key => $section) {
            array_push($response, new Metadata($section));
        }

        return $response;
    }

    /**
     * Get a specific Section Metadata from the database based on ID
     *
     * @param $id
     *
     * @return Metadata
     */
    public function getSectionMetadataById($id)
    {
        $params = [
            'id' => $id
        ];

        if ($record = $this->sectionMetadataRecord->find()->where($params)->one()) {
            $metadata = new Metadata($record->getAttributes());

            return $metadata;
        }

        return null;
    }

    /**
     * Get all Sections Metadata from the database based on ID
     *
     * @param $id
     *
     * @return BaseModel|Metadata
     */
    public function getSectionsMetadataById($id)
    {
        $params = [
            'id' => $id
        ];

        return $this->sectionMetadataRecord->find()->where($params)->all();
    }

    /**
     * @param $elementTable
     * @param $handle
     *
     * @return Metadata
     * @throws \craft\errors\SiteNotFoundException
     */
    public function getSectionMetadataByUniqueKey($elementTable, $handle)
    {
        $sectionsRegistered = SproutSeo::$app->sectionMetadata->getUrlEnabledSectionTypes();
        $urlEnabledSectionId = null;
        $query = (new Query())
            ->select('*')
            ->from(['{{%sproutseo_sitemaps}}']);

        if ($elementTable == 'sproutseo_section') {
            $query->where('handle=:handle', [':handle' => $handle]);
        } else if (isset($sectionsRegistered[$elementTable])) {
            $sectionType = $sectionsRegistered[$elementTable];
            $elementSection = null;

            foreach ($sectionType->urlEnabledSections as $uniqueKey => $urlEnabledSection) {
                if ($urlEnabledSection->sectionMetadata->handle == $handle) {
                    $elementSection = $urlEnabledSection;
                    break;
                }
            }

            if ($elementSection) {
                $urlEnabledSectionId = $elementSection->sectionMetadata->urlEnabledSectionId;
            }

            $query->where('urlEnabledSectionId=:urlEnabledSectionId', [':urlEnabledSectionId' => $urlEnabledSectionId]);

            $query->andWhere('type=:type', [':type' => $elementTable]);
        }

        $results = $query->one();

        if (!isset($results)) {
            return new Metadata();
        }

        $model = new Metadata($results);

        $model->robots = $model->robots ?? SproutSeoOptimizeHelper::prepareRobotsMetadataForSettings($model->robots);
        $model->position = SproutSeoOptimizeHelper::prepareGeoPosition($model);

        return $model;
    }

    /**
     * @param      $urlEnabledSection
     * @param null $siteId
     *
     * @return Metadata
     * @throws \craft\errors\SiteNotFoundException
     */
    public function getSectionMetadataByInfo($urlEnabledSection, $siteId = null)
    {
        if (!$siteId) {
            $site = Craft::$app->getSites()->getPrimarySite();
            $siteId = $site->id;
        }

        $type = $urlEnabledSection->type->getElementTableName();
        $urlEnabledSectionId = SproutSeo::$app->optimize->urlEnabledSection->id;

        $params = [
            'section.type' => $type,
            'section.urlEnabledSectionId' => $urlEnabledSectionId,
            'siteId' => $siteId
        ];

        $metadata = new Metadata();

        // @todo - validate if maybe could be data on the primary site
        if ($record = $this->sectionMetadataRecord->find()->where($params)->one()) {
            $metadata = new Metadata($record->getAttributes());
            // now add section properties
            $metadata->setAttributes($record->sectionMetadata->getAttributes(), false);
        }

        $metadata->robots = $metadata->robots ?? SproutSeoOptimizeHelper::prepareRobotsMetadataForSettings($metadata->robots);
        $metadata->position = SproutSeoOptimizeHelper::prepareGeoPosition($metadata);

        if (Craft::$app->request->getIsSiteRequest()) {
            //@todo
            //$metadata->optimizedTitle = Craft::$app->view->renderObjectTemplate($metadata->optimizedTitle, $elementmetadata);
        }

        return $metadata;
    }

    /**
     * @param Metadata $model
     *
     * @return bool
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function saveSectionMetadata(Metadata $model): bool
    {
        $isNewSection = !$model->id;

        if (!$isNewSection) {
            if (null === ($sectionRecord = SectionMetadataRecord::findOne($model->id))) {
                throw new \Exception(Craft::t('sprout-seo', 'Can\'t find Section Metadata with ID "{id}"', [
                    'id' => $model->id
                ]));
            }
        } else {
            $sectionRecord = new SectionMetadataRecord;
        }

        if ($model->isCustom) {
            $model->setScenario('customSection');

            if (!$model->validate()) {
                return false;
            }
        }

        $model->validate();

        if ($model->getErrors()) {
            return false;
        }

        $sectionRecord->siteId = $model->siteId;
        $sectionRecord->urlEnabledSectionId = $model->urlEnabledSectionId;
        $sectionRecord->isCustom = $model->isCustom ?? false;
        $sectionRecord->enabled = $model->enabled ?? false;
        $sectionRecord->type = $model->type;
        $sectionRecord->name = $model->name;
        $sectionRecord->handle = $model->handle;
        $sectionRecord->uri = $model->uri;
        $sectionRecord->priority = $model->priority;
        $sectionRecord->changeFrequency = $model->changeFrequency;

        $transaction = Craft::$app->db->beginTransaction();

        try {
            $sectionRecord->save(false);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        // update id on model (for new records)
        $model->id = $sectionRecord->id;

        return true;
    }

    /**
     * @param MetadataSitemap $model
     *
     * @return bool
     * @throws \Exception
     * @throws \yii\db\Exception
     */
    public function saveSectionMetadataViaSitemapSection(MetadataSitemap $model)
    {
        if ($model->id) {
            if (null === ($record = SectionMetadataRecord::findOne($model->id))) {
                throw new \Exception(Craft::t('sprout-seo', 'Can\'t find Section Metadata with ID "{id}"', [
                    'id' => $model->id
                ]));
            }
        } else {
            $record = new SectionMetadataRecord();
        }
        $seoSettings = Craft::$app->plugins->getPlugin('sprout-seo')->getSettings();


        // Only override the values available to update on the Sitemap page, and the
        // primary values like name and handle if the record needs to be created.
        $record->id = $model->id;
        $record->name = $model->name;
        $record->siteId = $model->siteId;
        $record->enabledForSite = $model->enabledForSite;
        $record->handle = $model->handle;
        $record->type = $model->type;
        $record->urlEnabledSectionId = $model->urlEnabledSectionId;
        $record->uri = $model->uri;
        $record->priority = $model->priority;
        $record->changeFrequency = $model->changeFrequency;
        $record->enabled = $model->enabled;

        $transaction = Craft::$app->db->beginTransaction();

        if (!$record->save()) {
            $model->addErrors($record->getErrors());

            $transaction->rollBack();

            return false;
        }

        $transaction->commit();

        // Let's copy this site behavior to all the group
        if ($seoSettings->enableMultilingualSitemaps) {
            $site = Craft::$app->getSites()->getSiteById($record->siteId);
            $groupSites = Craft::$app->getSites()->getSitesByGroupId($site->groupId);
            // all sections saved for this site
            $rowsBehavior = SectionMetadataRecord::findAll(['siteId' => $site->id]);

            foreach ($rowsBehavior as $rowBehavior) {
                foreach ($groupSites as $groupSite) {
                    $sectionMetadata = SectionMetadataRecord::findOne([
                        'siteId' => $groupSite->id,
                        'type' => $rowBehavior->type,
                        'handle' => $rowBehavior->handle
                    ]);

                    if (is_null($sectionMetadata)) {
                        $sectionMetadata = new SectionMetadataRecord();
                    }

                    $sectionMetadata->name = $rowBehavior->name;
                    $sectionMetadata->siteId = $groupSite->id;
                    $sectionMetadata->enabledForSite = $rowBehavior->enabledForSite;
                    $sectionMetadata->handle = $rowBehavior->handle;
                    $sectionMetadata->type = $rowBehavior->type;
                    $sectionMetadata->urlEnabledSectionId = $rowBehavior->urlEnabledSectionId;
                    $sectionMetadata->uri = $rowBehavior->uri;
                    $sectionMetadata->priority = $rowBehavior->priority;
                    $sectionMetadata->changeFrequency = $rowBehavior->changeFrequency;
                    $sectionMetadata->enabled = $rowBehavior->enabled;

                    $sectionMetadata->save();
                }
            }
        }

        $model->id = $record->id;

        return true;
    }

    public function getTransforms()
    {
        $options = [
            '' => Craft::t('sprout-seo', 'None')
        ];

        array_push($options, ['optgroup' => Craft::t('sprout-seo', 'Default Transforms')]);

        $options['sproutSeo-socialSquare'] = Craft::t('sprout-seo', 'Square – 400x400');
        $options['sproutSeo-ogRectangle'] = Craft::t('sprout-seo', 'Rectangle – 1200x630 – Open Graph');
        $options['sproutSeo-twitterRectangle'] = Craft::t('sprout-seo', 'Rectangle – 1024x512 – Twitter Card');

        $transforms = Craft::$app->assetTransforms->getAllTransforms();

        if (count($transforms)) {
            array_push($options, ['optgroup' => Craft::t('sprout-seo', 'Custom Transforms')]);

            foreach ($transforms as $transform) {
                $options[$transform->handle] = $transform->name;
            }
        }

        return $options;
    }

    /**
     * Delete a Section Metadata by ID
     *
     * @param null $id
     *
     * @return bool
     * @throws \yii\db\Exception
     */
    public function deleteSectionMetadataById($id = null)
    {
        $sectionMetadataRecord = SectionMetadataRecord::findOne($id);

        if (!$sectionMetadataRecord) {
            return false;
        }

        $affectedRows = Craft::$app->getDb()->createCommand()
            ->delete('{{%sproutseo_sitemaps}}', [
                'id' => $id
            ])
            ->execute();

        return (bool)$affectedRows;
    }

    public function getRegisteredUrlEnabledSectionsEvent()
    {
        $event = new RegisterUrlEnabledSectionTypesEvent([
            'urlEnabledSectionTypes' => []
        ]);

        $this->trigger(self::EVENT_REGISTER_URL_ENABLED_SECTION_TYPES, $event);

        $registeredUrlEnabledSectionsTypes = $event->urlEnabledSectionTypes;

        return $registeredUrlEnabledSectionsTypes;
    }
}
