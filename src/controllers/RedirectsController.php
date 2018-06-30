<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\controllers;

use barrelstrength\sproutseo\elements\Redirect;
use barrelstrength\sproutseo\SproutSeo;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use Craft;

use craft\web\Response;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

/**
 * Redirects controller
 */
class RedirectsController extends Controller
{
    /**
     * @param string|null $siteHandle
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws \craft\errors\SiteNotFoundException
     */
    public function actionRedirectsIndexTemplate(string $siteHandle = null): Response
    {
        if ($siteHandle === null) {
            $primarySite = Craft::$app->getSites()->getPrimarySite();
            $siteHandle = $primarySite->handle;
        }

        $currentSite = Craft::$app->getSites()->getSiteByHandle($siteHandle);

        if (!$currentSite->hasUrls) {
            throw new ForbiddenHttpException(Craft::t('sprout-seo', 'Unable to add redirect. {site} Site does not have URLs enabled.', [
                'site' => $currentSite
            ]));
        }

        $editableSiteIds = Craft::$app->getSites()->getEditableSiteIds();

        // Make sure the user has permission to edit that site
        if (!in_array($currentSite->id, $editableSiteIds, false)) {
            throw new ForbiddenHttpException(Craft::t('sprout-seo', 'User not permitted to edit content for this site.'));
        }

        $seoSettings = Craft::$app->plugins->getPlugin('sprout-seo')->getSettings();

        // Get enabled IDs. Remove any disabled IDS.
        // @todo - should we merge these settings with the Site Enabled/Disabled settings right here?
        $enabledSiteIds = array_filter($seoSettings->siteSettings);

        return $this->renderTemplate('sprout-base-seo/redirects', [
            'currentSite' => $currentSite,
            'baseUrl' => rtrim(Craft::getAlias($currentSite->baseUrl), '/').'/',
            'enabledSiteIds' => $enabledSiteIds
        ]);
    }

    /**
     * Edit a Redirect
     *
     * @param int|null      $redirectId The redirect's ID, if editing an existing redirect.
     * @param Redirect|null $redirect   The redirect send back by setRouteParams if any errors on saveRedirect
     * @param string|null   $siteHandle
     *
     * @return \yii\web\Response
     * @throws ForbiddenHttpException
     * @throws HttpException
     * @throws \ReflectionException
     * @throws \craft\errors\SiteNotFoundException
     */
    public function actionEditRedirect(int $redirectId = null, string $siteHandle = null, Redirect $redirect = null)
    {
        if ($siteHandle === null) {
            $primarySite = Craft::$app->getSites()->getPrimarySite();
            $siteHandle = $primarySite->handle;
        }

        $currentSite = Craft::$app->getSites()->getSiteByHandle($siteHandle);

        if (!$currentSite->hasUrls) {
            throw new ForbiddenHttpException(Craft::t('sprout-seo', 'Unable to add redirect. {site} Site does not have URLs enabled.', [
                'site' => $currentSite
            ]));
        }

        $methodOptions = SproutSeo::$app->redirects->getMethods();

        // Now let's set up the actual redirect
        if ($redirect === null) {
            if ($redirectId !== null) {

                $redirect = SproutSeo::$app->redirects->getRedirectById($redirectId, $currentSite->id);

                if (!$redirect) {
                    throw new HttpException(404);
                }
            } else {
                $redirect = new Redirect();
                $redirect->siteId = $currentSite->id;
            }
        }

        $continueEditingUrl = 'sprout-seo/redirects/'.$currentSite->handle.'/edit/{id}';

        $crumbs = [
            [
                'label' => Craft::t('sprout-seo', 'Redirects'),
                'url' => UrlHelper::cpUrl('redirects')
            ]
        ];

        return $this->renderTemplate('sprout-base-seo/redirects/_edit', [
            'currentSite' => $currentSite,
            'redirect' => $redirect,
            'methodOptions' => $methodOptions,
            'crumbs' => $crumbs,
            'continueEditingUrl' => $continueEditingUrl,
            'siteHandle' => $siteHandle
        ]);
    }

    /**
     * Saves a Redirect
     *
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws \Throwable
     */
    public function actionSaveRedirect()
    {
        $this->requirePostRequest();

        $redirectId = Craft::$app->getRequest()->getBodyParam('redirectId');

        if ($redirectId) {
            $redirect = SproutSeo::$app->redirects->getRedirectById($redirectId);

            if (!$redirect) {
                throw new Exception(Craft::t('sprout-seo', 'No redirect exists with the ID “{id}”', [
                    'id' => $redirectId
                ]));
            }
        } else {
            $redirect = new Redirect();
        }

        $primarySiteId = Craft::$app->getSites()->getPrimarySite()->id;

        // Set the event attributes, defaulting to the existing values for
        // whatever is missing from the post data
        $redirect->siteId = Craft::$app->getRequest()->getBodyParam('siteId') ?? $primarySiteId;
        $redirect->oldUrl = Craft::$app->getRequest()->getBodyParam('oldUrl', $redirect->oldUrl);
        $redirect->newUrl = Craft::$app->getRequest()->getBodyParam('newUrl');
        $redirect->method = Craft::$app->getRequest()->getBodyParam('method');
        $redirect->regex = Craft::$app->getRequest()->getBodyParam('regex');

        if (!$redirect->regex) {
            $redirect->regex = 0;
        }

        $redirect->enabled = Craft::$app->getRequest()->getBodyParam('enabled');

        if (!SproutSeo::$app->redirects->saveRedirect($redirect)) {
            Craft::$app->getSession()->setError(Craft::t('sprout-seo', 'Couldn’t save redirect.'));

            // Send the event back to the template
            return Craft::$app->getUrlManager()->setRouteParams([
                'redirect' => $redirect
            ]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout-seo', 'Redirect saved.'));

        $this->redirectToPostedUrl($redirect);
    }

    /**
     * Deletes a Redirect
     *
     * @throws BadRequestHttpException
     * @throws \Throwable
     */
    public function actionDeleteRedirect()
    {
        $this->requirePostRequest();

        $redirectId = Craft::$app->getRequest()->getRequiredBodyParam('redirectId');

        if (Craft::$app->elements->deleteElementById($redirectId)) {
            Craft::$app->getSession()->setNotice(Craft::t('sprout-seo', 'Redirect deleted.'));
            $this->redirectToPostedUrl();
        } else {
            Craft::$app->getSession()->setError(Craft::t('sprout-seo', 'Couldn’t delete redirect.'));
        }
    }
}
