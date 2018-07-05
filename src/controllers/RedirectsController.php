<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\controllers;

use barrelstrength\sproutseo\elements\Redirect;
use barrelstrength\sproutseo\models\Settings;
use barrelstrength\sproutseo\SproutSeo;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use Craft;

use yii\web\Response;
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
     * @param int|null $baseSiteId
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws \craft\errors\SiteNotFoundException
     */
    public function actionRedirectsIndexTemplate(int $baseSiteId = null): Response
    {
        if ($baseSiteId === null) {
            $primarySite = Craft::$app->getSites()->getPrimarySite();
            $baseSite = SproutSeo::$app->redirects->getBaseSiteBySiteId($primarySite->id);
            $baseSiteId = $baseSite['id'];
        }

        $currentSite = SproutSeo::$app->redirects->getBaseSiteById($baseSiteId);

        if (!$currentSite) {
            throw new ForbiddenHttpException(Craft::t('sprout-seo', 'Something went wrong'));
        }

        return $this->renderTemplate('sprout-base-seo/redirects', [
            'currentSite' => $currentSite
        ]);
    }

    /**
     * Edit a Redirect
     *
     * @param int|null      $redirectId The redirect's ID, if editing an existing redirect.
     * @param Redirect|null $redirect   The redirect send back by setRouteParams if any errors on saveRedirect
     * @param int|null   $baseSiteId
     *
     * @return \yii\web\Response
     * @throws ForbiddenHttpException
     * @throws HttpException
     * @throws \craft\errors\SiteNotFoundException
     */
    public function actionEditRedirect($redirectId = null, int $baseSiteId = null, Redirect $redirect = null)
    {
        if ($baseSiteId === null) {
            $primarySite = Craft::$app->getSites()->getPrimarySite();
            $baseSite = SproutSeo::$app->redirects->getBaseSiteBySiteId($primarySite->id);
            $baseSiteId = $baseSite['id'];
        }

        $currentSite = SproutSeo::$app->redirects->getBaseSiteById($baseSiteId);

        if (!$currentSite) {
            throw new ForbiddenHttpException(Craft::t('sprout-seo', 'Something went wrong'));
        }

        $methodOptions = SproutSeo::$app->redirects->getMethods();

        // Now let's set up the actual redirect
        if ($redirect === null) {
            if ($redirectId !== null) {

                $redirect = SproutSeo::$app->redirects->getRedirectById($redirectId, $currentSite['siteId']);

                if (!$redirect) {
                    throw new HttpException(404);
                }
            } else {
                $redirect = new Redirect();
                $redirect->siteId = $currentSite['id'];
            }
        }

        $continueEditingUrl = 'sprout-seo/redirects/edit/{id}/'.$currentSite['id'];

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
            'continueEditingUrl' => $continueEditingUrl
        ]);
    }

    /**
     * Saves a Redirect
     *
     * @throws BadRequestHttpException
     * @throws Exception
     * @throws \Throwable
     */
    public function actionSaveRedirect() : Response
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
            Craft::$app->getUrlManager()->setRouteParams([
                'redirect' => $redirect
            ]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('sprout-seo', 'Redirect saved.'));

        return $this->redirectToPostedUrl($redirect);
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
