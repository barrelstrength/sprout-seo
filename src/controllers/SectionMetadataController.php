<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\controllers;

use barrelstrength\sproutseo\helpers\SproutSeoOptimizeHelper;
use barrelstrength\sproutseo\models\Metadata;
use barrelstrength\sproutseo\models\MetadataSitemap;
use barrelstrength\sproutseo\SproutSeo;
use craft\web\Controller;
use craft\elements\Asset;
use Craft;

use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;


/**
 * Class SectionMetadataController
 */
class SectionMetadataController extends Controller
{
    /**
     * @param string|null $siteHandle
     *
     * @return Response
     * @throws NotFoundHttpException
     * @throws \craft\errors\SiteNotFoundException
     */
    public function actionIndex(string $siteHandle = null): Response
    {
        $seoSettings = Craft::$app->plugins->getPlugin('sprout-seo')->getSettings();
        $enableMultilingualSitemaps = Craft::$app->getIsMultiSite() && $seoSettings->enableMultilingualSitemaps;

        // Get enabled IDs. Remove any disabled IDS.
        // @todo - should we merge these settings with the Site Enabled/Disabled settings right here?
        $enabledSiteIds = array_filter($seoSettings->siteSettings);
        $enabledSiteGroupIds = array_filter($seoSettings->groupSettings);

        if (!$enableMultilingualSitemaps && empty($enabledSiteIds)) {
            throw new NotFoundHttpException('No Sites are enabled for your Sitemap. Check your Craft Sites settings and Sprout SEO Sitemap Settings to enable a Site for your Sitemap.');
        }

        if ($enableMultilingualSitemaps && empty($enabledSiteGroupIds)) {
            throw new NotFoundHttpException('No Site Groups are enabled for your Sitemap. Check your Craft Sites settings and Sprout SEO Sitemap Settings to enable a Site Group for your Sitemap.');
        }

        $currentSite = null;
        $currentSiteGroup = null;
        $firstSiteInGroup = null;

        if (Craft::$app->getIsMultiSite()) {

            // Form Multi-Site we have to figure out which Site and Site Group matter
            if ($siteHandle !== null) {

                // If we have a handle, the Current Site and First Site in Group may be different
                $currentSite = Craft::$app->getSites()->getSiteByHandle($siteHandle);

                if (!$currentSite) {
                    throw new NotFoundHttpException('Invalid site handle: '.$siteHandle);
                }

                $currentSiteGroup = Craft::$app->sites->getGroupById($currentSite->groupId);
                $sitesInCurrentSiteGroup = Craft::$app->sites->getSitesByGroupId($currentSiteGroup->id);
                $firstSiteInGroup = $sitesInCurrentSiteGroup[0];
            } else {
                // If we don't have a handle, we'll load the first site in the first group
                // We'll assume that we have at least one site group and the Current Site will be the same as the First Site
                $allSiteGroups = Craft::$app->sites->getAllGroups();
                $currentSiteGroup = $allSiteGroups[0];
                $sitesInCurrentSiteGroup = Craft::$app->sites->getSitesByGroupId($currentSiteGroup->id);
                $firstSiteInGroup = $sitesInCurrentSiteGroup[0];
                $currentSite = $firstSiteInGroup;
            }
        } else {
            // For a single site, the primary site ID will do
            $currentSite = Craft::$app->getSites()->getPrimarySite();
            $firstSiteInGroup = $currentSite->id;
        }

        // @todo - I think we can remove this now
        // For multi-lingual sitemaps, get the Group ID and first site in that group
//        if ($enableMultilingualSitemaps) {
//
////            $firstGroupSiteId = null;
//            foreach ($enabledSiteGroupIds as $siteGroupId) {
//                $sites = Craft::$app->getSites()->getSitesByGroupId($siteGroupId);
//                foreach ($sites as $currentSite) {
//                    if (is_null($siteHandle) && is_null($firstGroupSiteId)) {
//                        $siteId = $currentSite->id;
//                        $firstGroupSiteId = $siteId;
//                    }
//                    if ($siteId == $currentSite->id) {
//                        $is404 = false;
//                    }
//                    array_push($enabledSiteIds, $currentSite->id);
//                }
//            }
//        }

//        if ($is404){
//            // The group site that allows to this siteHandle could be disabled in the Sprout SEO settings
//            throw new NotFoundHttpException('Invalid site handle: '.$siteHandle);
//        }

        return $this->renderTemplate('sprout-base-seo/sections', [
            'currentSite' => $currentSite,
            'firstSiteInGroup' => $firstSiteInGroup,
            'enabledSiteIds' => $enabledSiteIds,
            'enableMultilingualSitemaps' => $enableMultilingualSitemaps,

        ]);
    }

    /**
     * Loads a Section Metadata Edit template
     *
     * @param int|null      $sectionMetadataId
     * @param string|null   $siteHandle
     * @param Metadata|null $sectionMetadata
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \craft\errors\SiteNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionSectionMetadataEditTemplate(int $sectionMetadataId = null, string $siteHandle = null, Metadata $sectionMetadata = null)
    {
        if (Craft::$app->getIsMultiSite()) {
            // Get the sites the user is allowed to edit
            $editableSiteIds = Craft::$app->getSites()->getEditableSiteIds();

            if (empty($editableSiteIds)) {
                throw new ForbiddenHttpException('User not permitted to edit content in any sites');
            }

            // Editing a specific site?
            if ($siteHandle !== null) {
                $site = Craft::$app->getSites()->getSiteByHandle($siteHandle);

                if (!$site) {
                    throw new NotFoundHttpException('Invalid site handle: '.$siteHandle);
                }

                // Make sure the user has permission to edit that site
                if (!in_array($site->id, $editableSiteIds, false)) {
                    throw new ForbiddenHttpException('User not permitted to edit content in this site');
                }
            } else {
                // Are they allowed to edit the current site?
                if (in_array(Craft::$app->getSites()->currentSite->id, $editableSiteIds, false)) {
                    $site = Craft::$app->getSites()->currentSite;
                } else {
                    // Just use the first site they are allowed to edit
                    $site = Craft::$app->getSites()->getSiteById($editableSiteIds[0]);
                }
            }
        } else {
            $site = Craft::$app->getSites()->getPrimarySite();
        }

        $isCustom = true;
        $sectionsRegistered = SproutSeo::$app->sectionMetadata->getUrlEnabledSectionTypes();

        // Get our Section Metadata Model
        if ($sectionMetadata == null && $sectionMetadataId) {
            $sectionMetadata = SproutSeo::$app->sectionMetadata->getSectionMetadataById(
                $sectionMetadataId
            );

            // Check if is a new site
            if (!$sectionMetadata) {
                // @todo - should we use getPrimarySite instead?
                $records = SproutSeo::$app->sectionMetadata->getSectionsMetadataById($sectionMetadataId);
                // let's take the default values for the new site
                if ($records) {
                    $record = $records[0];
                    $sectionMetadata = new Metadata();
                    $sectionMetadata->id = $sectionMetadataId;
                    $sectionMetadata->siteId = $site->id;
                    $sectionMetadata->urlEnabledSectionId = $record->sectionMetadata->urlEnabledSectionId;
                    $sectionMetadata->type = $record->sectionMetadata->type;
                    $sectionMetadata->name = $record->sectionMetadata->name;
                    $sectionMetadata->handle = $record->sectionMetadata->handle;
                    $sectionMetadata->enabled = $record->sectionMetadata->enabled;
                } else {
                    throw new NotFoundHttpException('Invalid section id: '.$sectionMetadataId);
                }
            }
        } else {
            // custom sections
            $sectionMetadata = new Metadata();
            $sectionMetadata->siteId = $site->id;
        }

        $isNew = $sectionMetadata->id != null ? false : true;
        $urlEnabledSectionType = null;

        $twitterImageElements = [];
        $ogImageElements = [];
        $metaImageElements = [];

        // Let's get the handle and url from the Craft cms database to don't store this information
        if ($sectionMetadata->id) {
            if (isset($sectionsRegistered[$sectionMetadata->type])) {
                $sectionType = $sectionsRegistered[$sectionMetadata->type];
                $uniqueKey = $sectionType->getId().'-'.$sectionMetadata->urlEnabledSectionId;
                $elementSection = $sectionType->urlEnabledSections[$uniqueKey];
                // let's update the handle and the url
                $sectionMetadata->handle = $sectionMetadata->type.':'.$elementSection->sectionMetadata->handle;
                $sectionMetadata->uri = $elementSection->sectionMetadata->uri;
            }
        }

        if ($sectionMetadata->type && $sectionMetadata->urlEnabledSectionId) {
            $isCustom = false;
        }

        // Set up our asset fields
        if ($sectionMetadata->optimizedImage) {
            $asset = Craft::$app->elements->getElementById($sectionMetadata->optimizedImage);
            $metaImageElements = [$asset];
        }

        if ($sectionMetadata->ogImage) {
            $asset = Craft::$app->elements->getElementById($sectionMetadata->ogImage);
            $ogImageElements = [$asset];
        }

        if ($sectionMetadata->twitterImage) {
            $asset = Craft::$app->elements->getElementById($sectionMetadata->twitterImage);
            $twitterImageElements = [$asset];
        }

        $sectionMetadata->robots = $sectionMetadata->robots ? SproutSeoOptimizeHelper::prepareRobotsMetadataForSettings($sectionMetadata->robots) : SproutSeoOptimizeHelper::prepareRobotsMetadataForSettings($sectionMetadata->robots);

        // Set assetsSourceExists
        $sources = Craft::$app->assets->findFolders();
        $assetsSourceExists = count($sources);

        //get optimized settings
        $settings = SproutSeoOptimizeHelper::getDefaultFieldTypeSettings();

        // Set elementType
        $elementType = Asset::class;

        if (!$isNew && !$isCustom) {
            $urlEnabledSectionType = SproutSeo::$app->sectionMetadata->getUrlEnabledSectionTypeByType($sectionMetadata->type);

            $type = $sectionMetadata->type;
            $urlEnabledSectionId = $sectionMetadata->urlEnabledSectionId;
            $urlEnabledSection = $urlEnabledSectionType->urlEnabledSections[$type.'-'.$urlEnabledSectionId];
            SproutSeo::$app->optimize->urlEnabledSection = $urlEnabledSection;
        }

        SproutSeo::$app->optimize->globals = SproutSeo::$app->globalMetadata->getGlobalMetadata();

        $prioritizedMetadata = SproutSeo::$app->optimize->getPrioritizedMetadataModel();

        $continueEditingUrl = 'sprout-seo/sections/{id}';

        if (Craft::$app->getIsMultiSite() && Craft::$app->getSites()->currentSite->id != $site->id) {
            $continueEditingUrl = 'sprout-seo/sections/{id}/'.$site->handle;
        }

        $revisionLabel = $site->name.' - Current';

        return $this->renderTemplate('sprout-base-seo/sections/_edit', [
            'sectionMetadataId' => $sectionMetadataId,
            'sectionMetadata' => $sectionMetadata,
            'metaImageElements' => $metaImageElements,
            'ogImageElements' => $ogImageElements,
            'twitterImageElements' => $twitterImageElements,
            'assetsSourceExists' => $assetsSourceExists,
            'elementType' => $elementType,
            'settings' => $settings,
            'isCustom' => $isCustom,
            'isNew' => $isNew or $isCustom,
            'urlEnabledSectionType' => $urlEnabledSectionType,
            'prioritizedMetadata' => $prioritizedMetadata,
            'continueEditingUrl' => $continueEditingUrl,
            'revisionLabel' => $revisionLabel
        ]);
    }

    /**
     * Saves a Section Metadata Section
     *
     * @return null|Response
     * @throws \Throwable
     * @throws \craft\errors\SiteNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveSectionMetadata()
    {
        $this->requirePostRequest();

        $model = new Metadata();
        // the request could be from our ajax request

        $model->enabledForSite = Craft::$app->getRequest()->getBodyParam('enabledForSite') ?? false;

        $sectionMetadata = Craft::$app->getRequest()->getBodyParam('sproutseo.metadata');
        $model->siteId = $sectionMetadata->siteId ?? Craft::$app->getSites()->getPrimarySite()->id;

        // Check if this is a new or existing Section Metadata
        $sectionMetadata['id'] = $sectionMetadata['id'] ?? null;

        // Convert Checkbox Array into comma-delimited String
        if (isset($sectionMetadata['robots'])) {
            $sectionMetadata['robots'] = SproutSeoOptimizeHelper::prepareRobotsMetadataValue($sectionMetadata['robots']);
        }

        // let's validate to send the image id instead of an array
        $sectionMetadata = $this->_validateImages($sectionMetadata);
        $model->setAttributes($sectionMetadata, false);

        $model = SproutSeoOptimizeHelper::updateOptimizedAndAdvancedMetaValues($model);

        if (!SproutSeo::$app->sectionMetadata->saveSectionMetadata($model)) {
            if (Craft::$app->request->getAcceptsJson()) {
                return $this->asJson([
                    'errors' => $model->getErrors(),
                ]);
            }
            Craft::$app->getSession()->setError(Craft::t('sprout-seo', "Couldn't save the Section Metadata."));

            Craft::$app->getUrlManager()->setRouteParams([
                'sectionMetadata' => $model
            ]);

            return null;
        }

        if (Craft::$app->request->getAcceptsJson()) {
            return $this->asJson([
                'success' => true,
                'sectionMetadata' => $model
            ]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout-seo', 'Section Metadata saved.'));

        return $this->redirectToPostedUrl($model);
    }

    /**
     * Saves a Section Metadata Section
     *
     * @todo - Refactor
     *         can we merge with actionSaveSectionMetadata?
     *
     * @return \yii\web\Response
     * @throws \Exception
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveSectionMetadataViaSitemapSection()
    {
        $this->requireAcceptsJson();

        $sectionMetadata = Craft::$app->getRequest()->getBodyParam('sproutseo.metadata');

        $model = new MetadataSitemap($sectionMetadata);

        if (!SproutSeo::$app->sectionMetadata->saveSectionMetadataViaSitemapSection($model)) {
            return $this->asJson([
                'errors' => $model->getErrors()
            ]);
        }

        return $this->asJson([
            'success' => true,
            'sectionMetadata' => $model
        ]);
    }

    /**
     * Deletes a Section Metadata Section
     *
     * @return \yii\web\Response
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDeleteSectionMetadataById()
    {
        $this->requirePostRequest();

        $sectionMetadataId = Craft::$app->getRequest()->getRequiredBodyParam('id');

        $result = SproutSeo::$app->sectionMetadata->deleteSectionMetadataById($sectionMetadataId);

        if (Craft::$app->request->getAcceptsJson()) {
            return $this->asJson([
                'success' => $result >= 0 ? true : false
            ]);
        }

        $this->redirectToPostedUrl();
    }

    /**
     * @param $sectionMetadata
     *
     * @return mixed
     */
    private function _validateImages($sectionMetadata)
    {
        $image = null;

        if (isset($sectionMetadata['optimizedImage'][0])) {
            $image = $sectionMetadata['optimizedImage'][0];
        }

        $sectionMetadata['optimizedImage'] = $image;

        if (isset($sectionMetadata['ogImage'][0])) {
            $sectionMetadata['ogImage'] = $sectionMetadata['ogImage'][0];
        } else {
            $sectionMetadata['ogImage'] = $image;
        }

        if (isset($sectionMetadata['twitterImage'][0])) {
            $sectionMetadata['twitterImage'] = $sectionMetadata['twitterImage'][0];
        } else {
            $sectionMetadata['twitterImage'] = $image;
        }

        return $sectionMetadata;
    }
}
