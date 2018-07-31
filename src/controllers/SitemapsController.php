<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\controllers;

use barrelstrength\sproutseo\models\Settings;
use barrelstrength\sproutseo\models\SitemapSection;
use barrelstrength\sproutseo\sectiontypes\NoSection;
use barrelstrength\sproutseo\SproutSeo;
use craft\web\Controller;
use Craft;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * Class SitemapsController
 */
class SitemapsController extends Controller
{
    /**
     * Renders the Sitemap Index Page
     *
     * @param string|null $siteHandle
     *
     * @return Response
     * @throws NotFoundHttpException
     * @throws \craft\errors\SiteNotFoundException
     */
    public function actionSitemapIndexTemplate(string $siteHandle = null): Response
    {
        /**
         * @var Settings $pluginSettings
         */
        $pluginSettings = Craft::$app->plugins->getPlugin('sprout-seo')->getSettings();
        $enableMultilingualSitemaps = Craft::$app->getIsMultiSite() && $pluginSettings->enableMultilingualSitemaps;

        // Get Enabled Site IDs. Remove any disabled IDS.
        $enabledSiteIds = array_filter($pluginSettings->siteSettings);
        $enabledSiteGroupIds = array_filter($pluginSettings->groupSettings);

        if (!$enableMultilingualSitemaps && empty($enabledSiteIds)) {
            throw new NotFoundHttpException('No Sites are enabled for your Sitemap. Check your Craft Sites settings and Sprout SEO Sitemap Settings to enable a Site for your Sitemap.');
        }

        if ($enableMultilingualSitemaps && empty($enabledSiteGroupIds)) {
            throw new NotFoundHttpException('No Site Groups are enabled for your Sitemap. Check your Craft Sites settings and Sprout SEO Sitemap Settings to enable a Site Group for your Sitemap.');
        }

        // Get all Editable Sites for this user that also have editable Sitemaps
        $editableSiteIds = Craft::$app->getSites()->getEditableSiteIds();

        // For per-site sitemaps, only display the Sites enabled in the Sprout SEO settings
        if ($enableMultilingualSitemaps === false) {
            $editableSiteIds = array_intersect($enabledSiteIds, $editableSiteIds);
        } else {
            $siteIdsFromEditableGroups = [];

            foreach ($enabledSiteGroupIds as $enabledSiteGroupId) {
                $enabledSitesInGroup = Craft::$app->sites->getSitesByGroupId($enabledSiteGroupId);
                foreach ($enabledSitesInGroup as $enabledSites) {
                    $siteIdsFromEditableGroups[] = (int)$enabledSites->id;
                }
            }

            $editableSiteIds = array_intersect($siteIdsFromEditableGroups, $editableSiteIds);
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

        $urlEnabledSectionTypes = SproutSeo::$app->sitemaps->getUrlEnabledSectionTypesForSitemaps($currentSite->id);

        $customSections = SproutSeo::$app->sitemaps->getCustomSitemapSections($currentSite->id);

        return $this->renderTemplate('sprout-base-seo/sitemaps', [
            'currentSite' => $currentSite,
            'firstSiteInGroup' => $firstSiteInGroup,
            'editableSiteIds' => $editableSiteIds,
            'enableMultilingualSitemaps' => $enableMultilingualSitemaps,
            'urlEnabledSectionTypes' => $urlEnabledSectionTypes,
            'customSections' => $customSections
        ]);
    }

    /**
     * Renders a Sitemap Edit Page
     *
     * @param int|null            $sitemapSectionId
     * @param string|null         $siteHandle
     * @param SitemapSection|null $sitemapSection
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionSitemapEditTemplate(int $sitemapSectionId = null, string $siteHandle = null, SitemapSection $sitemapSection = null)
    {
        if ($siteHandle === null) {
            throw new NotFoundHttpException('Unable to find site with handle: '.$siteHandle);
        }

        $currentSite = Craft::$app->getSites()->getSiteByHandle($siteHandle);

        $editableSiteIds = Craft::$app->getSites()->getEditableSiteIds();

        // Make sure the user has permission to edit that site
        if (!in_array($currentSite->id, $editableSiteIds, false)) {
            throw new ForbiddenHttpException('User not permitted to edit content for this site.');
        }

        if (!$sitemapSection) {
            if ($sitemapSectionId) {
                $sitemapSection = SproutSeo::$app->sitemaps->getSitemapSectionById($sitemapSectionId);
            } else {
                $sitemapSection = new SitemapSection();
                $sitemapSection->siteId = $currentSite->id;
                $sitemapSection->type = NoSection::class;
            }
        }

        $continueEditingUrl = 'sprout-seo/sitemaps/edit/{id}/'.$currentSite->handle;

        $tabs = [
            [
                'label' => 'Custom Page',
                'url' => '#tab1',
                'class' => null,
            ]
        ];

        return $this->renderTemplate('sprout-base-seo/sitemaps/_edit', [
            'currentSite' => $currentSite,
            'sitemapSection' => $sitemapSection,
            'continueEditingUrl' => $continueEditingUrl,
            'tabs' => $tabs
        ]);
    }

    /**
     * Saves a Sitemap Section
     *
     * @return null|Response
     * @throws \Throwable
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSaveSitemapSection()
    {
        $this->requirePostRequest();

        $sitemapSection = new SitemapSection();
        $sitemapSection->id = Craft::$app->getRequest()->getBodyParam('id', null);
        $sitemapSection->siteId = Craft::$app->getRequest()->getBodyParam('siteId');
        $sitemapSection->urlEnabledSectionId = Craft::$app->getRequest()->getBodyParam('urlEnabledSectionId', null);
        $sitemapSection->uri = Craft::$app->getRequest()->getBodyParam('uri');
        $sitemapSection->type = Craft::$app->getRequest()->getBodyParam('type');
        $sitemapSection->priority = Craft::$app->getRequest()->getBodyParam('priority');
        $sitemapSection->changeFrequency = Craft::$app->getRequest()->getBodyParam('changeFrequency');
        $sitemapSection->enabled = Craft::$app->getRequest()->getBodyParam('enabled');

        if (!SproutSeo::$app->sitemaps->saveSitemapSection($sitemapSection)) {
            if (Craft::$app->request->getAcceptsJson()) {
                return $this->asJson([
                    'errors' => $sitemapSection->getErrors(),
                ]);
            }
            Craft::$app->getSession()->setError(Craft::t('sprout-seo', "Couldn't save the Sitemap."));

            Craft::$app->getUrlManager()->setRouteParams([
                'sitemapSection' => $sitemapSection
            ]);

            return null;
        }

        if (Craft::$app->request->getAcceptsJson()) {
            return $this->asJson([
                'success' => true,
                'sitemapSection' => $sitemapSection
            ]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout-seo', 'Sitemap saved.'));

        return $this->redirectToPostedUrl($sitemapSection);
    }

    /**
     * Deletes a Sitemap Section
     *
     * @return \yii\web\Response
     * @throws \yii\db\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDeleteSitemapById(): Response
    {
        $this->requirePostRequest();

        $sitemapSectionId = Craft::$app->getRequest()->getRequiredBodyParam('id');

        $result = SproutSeo::$app->sitemaps->deleteSitemapSectionById($sitemapSectionId);

        if (Craft::$app->request->getAcceptsJson()) {
            return $this->asJson([
                'success' => $result >= 0
            ]);
        }

        return $this->redirectToPostedUrl();
    }
}
