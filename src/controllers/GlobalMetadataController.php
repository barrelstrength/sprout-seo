<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\controllers;

use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbasefields\models\Address;
use barrelstrength\sproutbasefields\models\Address as AddressModel;
use barrelstrength\sproutbasefields\services\AddressFormatter;
use barrelstrength\sproutbasefields\SproutBaseFields;
use barrelstrength\sproutseo\models\Globals;
use barrelstrength\sproutseo\SproutSeo;
use Craft;
use craft\errors\SiteNotFoundException;
use craft\helpers\DateTimeHelper;
use craft\helpers\Json;
use craft\helpers\Template;
use craft\web\Controller;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
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

        $address = $globals->identity['address'] ?? null;
        $addressDisplayHtml = '';

        $addressModel = new Address();
        $addressFormatter = new AddressFormatter();

        if ($address) {
            $addressModel->setAttributes($address, false);

            $addressFormatter->setCountryCode($addressModel->countryCode);
            $addressFormatter->setAddressModel($addressModel);

            $addressDisplayHtml = $addressFormatter->getAddressDisplayHtml($addressModel);
        }

        $countryInputHtml = $addressFormatter->getCountryInputHtml();
        $addressFormHtml = $addressFormatter->getAddressFormHtml();

        $isPro = SproutBase::$app->settings->isEdition('sprout-seo', SproutSeo::EDITION_PRO);

        $addressJson = $address ? Json::encode($address) : null;

        // Render the template!
        return $this->renderTemplate('sprout-seo/globals/'.$selectedTabHandle, [
            'globals' => $globals,
            'settings' => SproutSeo::$app->settings->getSettings(),
            'currentSite' => $currentSite,
            'selectedTabHandle' => $selectedTabHandle,
            'addressDisplayHtml' => Template::raw($addressDisplayHtml),
            'countryInputHtml' => Template::raw($countryInputHtml),
            'addressFormHtml' => Template::raw($addressFormHtml),
            'addressJson' => $addressJson,
            'isPro' => $isPro
        ]);
    }

    /**
     * Save Globals to the database
     *
     * @return null|Response
     * @throws BadRequestHttpException
     * @throws Throwable
     * @throws Exception
     * @throws \yii\db\Exception
     */
    public function actionSaveGlobalMetadata()
    {
        $this->requirePostRequest();

        $postData = Craft::$app->getRequest()->getBodyParam('sproutseo.globals');
        $globalColumn = Craft::$app->getRequest()->getBodyParam('globalColumn');

        $siteId = Craft::$app->getRequest()->getBodyParam('siteId');
        $address = Craft::$app->getRequest()->getBodyParam('address');

        // Adjust Address Field post data
        if ($address) {
            if (isset($address['delete']) && $address['delete']) {
                $postData['identity']['address'] = null;
            } else {
                unset(
                    $address['id'],
                    $address['fieldId'],
                    $address['delete']
                );
                $postData['identity']['address'] = $address;
            }
        }

        // Adjust Founding Date post data
        if (isset($postData['identity']['foundingDate'])) {
            $postData['identity']['foundingDate'] = DateTimeHelper::toDateTime($postData['identity']['foundingDate']);
        }

        // Adjust Schema Organization post data
        if (isset($postData['identity']['@type']) && $postData['identity']['@type'] === 'Person') {
            // Clean up our organization subtypes when the Person type is selected
            unset($postData['identity']['organizationSubTypes']);
        }

        $globals = new Globals($postData);
        $globals->siteId = $siteId;

        if (!SproutSeo::$app->globalMetadata->saveGlobalMetadata($globalColumn, $globals)) {
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
     * @throws Throwable
     * @throws \yii\db\Exception
     */
    public function actionSaveVerifyOwnership()
    {
        $this->requirePostRequest();

        $ownershipMeta = Craft::$app->getRequest()->getBodyParam('sproutseo.meta.ownership');
        $globalColumn = 'ownership';
        $siteId = Craft::$app->getRequest()->getBodyParam('siteId');

        $ownershipMetaWithKeys = null;

        // Remove empty items from multi-dimensional array
        if ($ownershipMeta) {
            $ownershipMeta = array_filter(array_map('array_filter', $ownershipMeta));

            foreach ($ownershipMeta as $key => $meta) {
                if (count($meta) === 3) {
                    $ownershipMetaWithKeys[$key]['service'] = $meta[0];
                    $ownershipMetaWithKeys[$key]['metaTag'] = $meta[1];
                    $ownershipMetaWithKeys[$key]['verificationCode'] = $meta[2];
                }
            }
        }

        $config[$globalColumn] = $ownershipMetaWithKeys;

        $globals = new Globals($config);
        $globals->siteId = $siteId;

        if (!SproutSeo::$app->globalMetadata->saveGlobalMetadata($globalColumn, $globals)) {
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
     * @return Response
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function actionGetAddressFormFieldsHtml(): Response
    {
        $this->requireAcceptsJson();
        $this->requirePostRequest();

        $addressFormatter = SproutBaseFields::$app->addressFormatter;

        $addressArray = Craft::$app->getRequest()->getBodyParam('addressJson');

        $address = new AddressModel();

        if ($addressArray) {
            $address->setAttributes($addressArray, false);

            // @todo - this won't populate correctly
            $address->siteId = $addressArray['siteId'] ?? null;
        }

        $addressDisplayHtml = $addressFormatter->getAddressDisplayHtml($address);

        return $this->asJson([
            'html' => $addressDisplayHtml
        ]);
    }
}
