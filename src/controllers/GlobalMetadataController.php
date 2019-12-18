<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\controllers;

use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbasefields\services\AddressFormatter;
use barrelstrength\sproutbasefields\SproutBaseFields;
use barrelstrength\sproutseo\helpers\OptimizeHelper;
use barrelstrength\sproutseo\models\Globals;
use barrelstrength\sproutseo\models\Metadata;
use barrelstrength\sproutseo\SproutSeo;
use CommerceGuys\Addressing\AddressFormat\AddressFormat;
use craft\errors\SiteNotFoundException;
use craft\helpers\Template;
use craft\web\Controller;
use Craft;
use craft\helpers\DateTimeHelper;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class GlobalMetadataController extends Controller
{
    /**
     * Renders Global Metadata edit pages
     *
     * @param string       $selectedTabHandle The global handle.
     * @param string|null  $siteHandle        The site handle, if specified.
     * @param Globals|null $globals           The global set being edited, if there were any validation errors.
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws SiteNotFoundException
     * @throws Exception
     */
    public function actionEditGlobalMetadata(string $selectedTabHandle, string $siteHandle = null, Globals $globals = null): Response
    {
        $currentSite = Craft::$app->getSites()->getPrimarySite();

        if (Craft::$app->getIsMultiSite()) {
            // Get the sites the user is allowed to edit
            $editableSiteIds = Craft::$app->getSites()->getEditableSiteIds();

            if (empty($editableSiteIds)) {
                throw new ForbiddenHttpException('User not permitted to edit content in any sites');
            }

            // Editing a specific site?
            if ($siteHandle !== null) {
                $currentSite = Craft::$app->getSites()->getSiteByHandle($siteHandle);

                if (!$currentSite) {
                    throw new NotFoundHttpException('Invalid site handle: '.$siteHandle);
                }

                // Make sure the user has permission to edit that site
                if (!in_array($currentSite->id, $editableSiteIds, false)) {
                    throw new ForbiddenHttpException('User not permitted to edit content in this site');
                }
            } else if (in_array($currentSite->id, $editableSiteIds, false)) {
                $currentSite = Craft::$app->getSites()->currentSite;
            } else {
                // Use the first site they are allowed to edit
                $currentSite = Craft::$app->getSites()->getSiteById($editableSiteIds[0]);
            }
        }

        if ($globals === null) {
            $globals = SproutSeo::$app->globalMetadata->getGlobalMetadata($currentSite);
            $globals->siteId = $currentSite->id;
        }

        $addressId = $globals->identity['addressId'] ?? null;

        $addressModel = SproutBaseFields::$app->addressField->getAddressById($addressId);

        $countryCode = $addressModel->countryCode;

        $addressFormatter = new AddressFormatter();
        $addressFormatter->setNamespace('address');
        $addressFormatter->setCountryCode($countryCode);
        $addressFormatter->setAddressModel($addressModel);

        $addressDisplayHtml = $addressId ? $addressFormatter->getAddressDisplayHtml($addressModel) : '';
        $countryInputHtml = $addressFormatter->getCountryInputHtml();
        $addressFormHtml = $addressFormatter->getAddressFormHtml();

        $isPro = SproutBase::$app->settings->isEdition('sprout-seo', SproutSeo::EDITION_PRO);

        // Render the template!
        return $this->renderTemplate('sprout-seo/globals/'.$selectedTabHandle, [
            'globals' => $globals,
            'currentSite' => $currentSite,
            'selectedTabHandle' => $selectedTabHandle,
            'addressDisplayHtml' => Template::raw($addressDisplayHtml),
            'countryInputHtml' => Template::raw($countryInputHtml),
            'addressFormHtml' => Template::raw($addressFormHtml),
            'isPro' => $isPro
        ]);
    }

    /**
     * Save Globals to the database
     *
     * @return null|Response
     * @throws BadRequestHttpException
     * @throws \Throwable
     * @throws Exception
     * @throws \yii\db\Exception
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionSaveGlobalMetadata()
    {
        $this->requirePostRequest();

        $postData = Craft::$app->getRequest()->getBodyParam('sproutseo.globals');
        $globalKeys = Craft::$app->getRequest()->getBodyParam('globalKeys');
        $siteId = Craft::$app->getRequest()->getBodyParam('siteId');

        $addressId = SproutBaseFields::$app->addressField->saveAddressByPost();

        if ($addressId) {
            $postData['identity']['addressId'] = $addressId;
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
     * @return Response
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

            Craft::$app->getUrlManager()->setRouteParams([
                'globals' => $globals
            ]);

            return null;
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout-seo', 'Globals saved.'));

        return $this->redirectToPostedUrl($globals);
    }

    /**
     * @param $postData
     *
     * @return Metadata
     * @throws Exception
     * @throws \yii\web\ServerErrorHttpException
     */
    public function populateGlobalMetadata($postData)
    {
        $siteId = Craft::$app->getRequest()->getBodyParam('siteId');
        $site = Craft::$app->getSites()->getSiteById($siteId);

        $oldGlobals = SproutSeo::$app->globalMetadata->getGlobalMetadata($site);
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

        $socialProfiles = $postData['social'] ?? $oldSocialProfiles ?? [];
        $twitterProfileName = OptimizeHelper::getTwitterProfileName($socialProfiles);

        $twitterCard = (isset($postData['settings']['defaultTwitterCard']) && $postData['settings']['defaultTwitterCard']) ? $postData['settings']['defaultTwitterCard'] : 'summary';

        $ogType = (isset($postData['settings']['defaultOgType']) && $postData['settings']['defaultOgType']) ? $postData['settings']['defaultOgType'] : 'article';

        $robots = $postData['robots'] ?? $oldGlobals->robots ?? [];
        $robotsMetaValue = OptimizeHelper::prepareRobotsMetadataValue($robots);

        $facebookPage = OptimizeHelper::getFacebookPage($socialProfiles);

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

            // @todo - Add location info
            $globalMetadata->region = "";
            $globalMetadata->placename = "";
            $globalMetadata->position = "";
            $globalMetadata->latitude = $postData['identity']['latitude'] ?? '';
            $globalMetadata->longitude = $postData['identity']['longitude'] ?? '';

            $globalMetadata->ogType = $ogType;
            $globalMetadata->ogSiteName = $identity['name'] ?? null;
            $globalMetadata->ogTitle = $optimizedTitle;
            $globalMetadata->ogDescription = $optimizedDescription;
            $globalMetadata->ogImage = $optimizedImage;
            $globalMetadata->ogImage = $optimizedImage;
            $globalMetadata->ogTransform = $identity['ogTransform'] ?? null;
            $globalMetadata->ogLocale = null;

            $globalMetadata->twitterCard = $twitterCard;
            $globalMetadata->twitterSite = $twitterProfileName;
            $globalMetadata->twitterCreator = $twitterProfileName;
            $globalMetadata->twitterTitle = $optimizedTitle;
            $globalMetadata->twitterDescription = $optimizedDescription;
            $globalMetadata->twitterImage = $optimizedImage;
            $globalMetadata->twitterTransform = $identity['twitterTransform'] ?? null;
        }

        return $globalMetadata;
    }
}
