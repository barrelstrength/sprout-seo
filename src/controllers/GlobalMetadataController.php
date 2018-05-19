<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\controllers;

use barrelstrength\sproutseo\helpers\SproutSeoOptimizeHelper;
use barrelstrength\sproutseo\models\Globals;
use barrelstrength\sproutseo\models\Metadata;
use barrelstrength\sproutseo\SproutSeo;
use barrelstrength\sproutbase\SproutBase;

use craft\web\Controller;
use Craft;
use craft\helpers\DateTimeHelper;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class GlobalMetadataController extends Controller
{

    /**
     * Edits a global metadata.
     *
     * @param string       $globalHandle The global handle.
     * @param string|null  $siteHandle   The site handle, if specified.
     * @param Globals|null $globals      The global set being edited, if there were any validation errors.
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \craft\errors\SiteNotFoundException
     * @throws \yii\base\Exception
     */
    public function actionEditGlobalMetadata(string $globalHandle, string $siteHandle = null, Globals $globals = null): Response
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

        $navItems = [
            'website-identity',
            'contacts',
            'social',
            'verify-ownership',
            'customization',
            'robots',
        ];

        if (!in_array($globalHandle, $navItems)) {
            throw new NotFoundHttpException('Invalid global handle: '.$globalHandle);
        }

        $globals = SproutSeo::$app->globalMetadata->getGlobalMetadata($site->id);
        $globals->siteId = $site->id;


        // Render the template!
        return $this->renderTemplate('sprout-seo/globals/'.$globalHandle, [
            'globals' => $globals,
            'globalHandle' => $globalHandle
        ]);
    }

    /**
     * Save Globals to the database
     *
     * @return null|Response
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws \yii\db\Exception
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionSaveGlobalMetadata()
    {
        $this->requirePostRequest();

        $postData = Craft::$app->getRequest()->getBodyParam('sproutseo.globals');
        $globalKeys = Craft::$app->getRequest()->getBodyParam('globalKeys');
        $siteId = Craft::$app->getRequest()->getBodyParam('siteId');

        $addressInfoId = SproutBase::$app->addressField->saveAddressByPost();

        if ($addressInfoId) {
            $postData['identity']['addressId'] = $addressInfoId;
        }

        $globalKeys = explode(',', $globalKeys);

        if (isset($postData['identity']['foundingDate'])) {
            $postData['identity']['foundingDate'] = DateTimeHelper::toDateTime($postData['identity']['foundingDate']);
        }

        $globals = new Globals($postData);
        $globals->siteId = $siteId;

        $globalMetadata = $this->populateGlobalMetadata($postData);

        $globals->meta = $globalMetadata;

        $identity = $globals->identity;

        if (isset($identity['@type']) && $identity['@type'] === 'Person') {
            // Clean up our organization subtypes when the Person type is selected
            unset($identity['organizationSubTypes']);

            $globals->identity = $identity;
        }

        if (!SproutSeo::$app->globalMetadata->saveGlobalMetadata($globalKeys, $globals)) {
            Craft::$app->getSession()->setError(Craft::t('sprout-seo', 'Unable to save globals.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'globals' => $globals
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout-seo', 'Globals saved.'));

        return $this->redirectToPostedUrl($globals);
    }

    /**
     * Save the Verify Ownership Structured Data to the database
     *
     * @return void|Response
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function actionSaveVerifyOwnership()
    {
        $this->requirePostRequest();

        $ownershipMeta = Craft::$app->getRequest()->getBodyParam('sproutseo.meta.ownership');
        $globalKeys = 'ownership';
        $siteId = Craft::$app->getRequest()->getBodyParam('siteId');

        // Remove empty items from multi-dimensional array
        $ownershipMeta = array_filter(array_map('array_filter', $ownershipMeta));

        $ownershipMetaWithKeys = [];

        foreach ($ownershipMeta as $key => $meta) {
            if (count($meta) === 3) {
                $ownershipMetaWithKeys[$key]['service'] = $meta[0];
                $ownershipMetaWithKeys[$key]['metaTag'] = $meta[1];
                $ownershipMetaWithKeys[$key]['verificationCode'] = $meta[2];
            }
        }

        $globals = new Globals([$globalKeys => $ownershipMetaWithKeys]);
        $globals->siteId = $siteId;

        if (!SproutSeo::$app->globalMetadata->saveGlobalMetadata([$globalKeys], $globals)) {
            Craft::$app->getSession()->setError(Craft::t('sprout-seo', 'Unable to save globals.'));

            return Craft::$app->getUrlManager()->setRouteParams([
                'globals' => $globals
            ]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout-seo', 'Globals saved.'));

        return $this->redirectToPostedUrl($globals);
    }

    /**
     * @param $postData
     *
     * @return Metadata
     * @throws \yii\base\Exception
     * @throws \yii\web\ServerErrorHttpException
     */
    public function populateGlobalMetadata($postData)
    {
        $settings = Craft::$app->plugins->getPlugin('sprout-seo')->getSettings();
        $site = Craft::$app->getSites()->currentSite;
        $info = Craft::$app->getInfo();
        $siteId = Craft::$app->getRequest()->getBodyParam('siteId');

        $oldGlobals = SproutSeo::$app->globalMetadata->getGlobalMetadata($siteId);
        $oldIdentity = $oldGlobals->identity ?? null;
        $identity = $postData['identity'] ?? $oldIdentity;
        $oldSocialProfiles = $oldGlobals !== null ? $oldGlobals->social : [];

        if (isset($postData['settings']['ogTransform'])) {
            $identity['ogTransform'] = $postData['settings']['ogTransform'];
        }

        if (isset($postData['settings']['twitterTransform'])) {
            $identity['twitterTransform'] = $postData['settings']['twitterTransform'];
        }

        $globalMetadata = new Metadata();
        $siteName = $info->name;

        $urlSetting = $postData['identity']['url'] ?? null;
        $siteUrl = SproutSeoOptimizeHelper::getGlobalMetadataSiteUrl($urlSetting);

        $socialProfiles = $postData['social'] ?? $oldSocialProfiles;
        $twitterProfileName = SproutSeoOptimizeHelper::getTwitterProfileName($socialProfiles);

        $twitterCard = (isset($postData['settings']['defaultTwitterCard']) && $postData['settings']['defaultTwitterCard']) ? $postData['settings']['defaultTwitterCard'] : 'summary';

        $ogType = (isset($postData['settings']['defaultOgType']) && $postData['settings']['defaultOgType']) ? $postData['settings']['defaultOgType'] : 'website';

        $robots = $postData['robots'] ?? $oldGlobals->robots;
        $robotsMetaValue = SproutSeoOptimizeHelper::prepareRobotsMetadataValue($robots);

        if ($settings->localeIdOverride) {
            //@todo
            $localeId = $settings->localeIdOverride;
        }

        $facebookPage = SproutSeoOptimizeHelper::getFacebookPage($socialProfiles);

        if ($facebookPage) {
            $globalMetadata->ogPublisher = $facebookPage;
        }

        if ($identity) {
            $identityName = $identity['name'] ?? null;
            $optimizedTitle = $identityName;
            $optimizedDescription = $identity['description'] ?? null;
            $optimizedImage = $identity['image'][0] ?? null;

            $globalMetadata->optimizedTitle = $optimizedTitle;
            $globalMetadata->optimizedDescription = $optimizedDescription;
            $globalMetadata->optimizedImage = $optimizedImage;

            $globalMetadata->title = $optimizedTitle;
            $globalMetadata->description = $optimizedDescription;
            $globalMetadata->keywords = $identity['keywords'] ?? null;

            $globalMetadata->robots = $robotsMetaValue;
            $globalMetadata->canonical = $siteUrl;

            // @todo - Add location info
            $globalMetadata->region = "";
            $globalMetadata->placename = "";
            $globalMetadata->position = "";
            $globalMetadata->latitude = $postData['identity']['latitude'] ?? '';
            $globalMetadata->longitude = $postData['identity']['longitude'] ?? '';

            $globalMetadata->ogType = $ogType;
            $globalMetadata->ogSiteName = $siteName;
            $globalMetadata->ogUrl = $siteUrl;
            $globalMetadata->ogTitle = $optimizedTitle;
            $globalMetadata->ogDescription = $optimizedDescription;
            $globalMetadata->ogImage = $optimizedImage;
            $globalMetadata->ogImage = $optimizedImage;
            $globalMetadata->ogTransform = $identity['ogTransform'] ?? null;
            $globalMetadata->ogLocale = $site->language;

            $globalMetadata->twitterCard = $twitterCard;
            $globalMetadata->twitterSite = $twitterProfileName;
            $globalMetadata->twitterCreator = $twitterProfileName;
            $globalMetadata->twitterUrl = $siteUrl;
            $globalMetadata->twitterTitle = $optimizedTitle;
            $globalMetadata->twitterDescription = $optimizedDescription;
            $globalMetadata->twitterImage = $optimizedImage;
            $globalMetadata->twitterTransform = $identity['twitterTransform'] ?? null;
        }

        return $globalMetadata;
    }
}
