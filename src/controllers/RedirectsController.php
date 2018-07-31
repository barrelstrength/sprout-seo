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
use craft\base\Element;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use Craft;

use yii\web\NotFoundHttpException;
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
     * @param string|null   $siteHandle
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws \craft\errors\SiteNotFoundException
     */
    public function actionRedirectsIndexTemplate($siteHandle = null): Response
    {
        if ($siteHandle === null) {
            $primarySite = Craft::$app->getSites()->getPrimarySite();
            $siteHandle = $primarySite->handle;
        }

        $currentSite = Craft::$app->getSites()->getSiteByHandle($siteHandle);

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
     * @param null          $redirectId
     * @param null          $siteHandle
     * @param Redirect|null $redirect
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \craft\errors\SiteNotFoundException
     */
    public function actionEditRedirect($redirectId = null, $siteHandle = null, Redirect $redirect = null)
    {
        if ($siteHandle === null)
        {
            $primarySite = Craft::$app->getSites()->getPrimarySite();
            $siteHandle = $primarySite->handle;
        }

        $currentSite = Craft::$app->getSites()->getSiteByHandle($siteHandle);

        if (!$currentSite) {
            throw new ForbiddenHttpException(Craft::t('sprout-seo', 'Unable to identify current site.'));
        }

        $methodOptions = SproutSeo::$app->redirects->getMethods();

        // Now let's set up the actual redirect
        if ($redirect === null) {
            if ($redirectId !== null) {

                $redirect = SproutSeo::$app->redirects->getRedirectById($redirectId, $currentSite->id);

                if (!$redirect) {
                    throw new NotFoundHttpException(Craft::t('sprout-seo', 'Unable to find a Redirect with the given id: {id}', [
                        'id' => $redirectId
                    ]));
                }

                if (!$redirect){
                    $redirect = new Redirect();
                    $redirect->id = $redirectId;
                }

                $redirect->siteId = $currentSite->id;
            } else {
                $redirect = new Redirect();
                $redirect->siteId = $currentSite->id;
            }
        }

        $redirect->newUrl = $redirect->newUrl === null ? '' : $redirect->newUrl;

        $continueEditingUrl = 'sprout-seo/redirects/edit/{id}/'.$currentSite->handle;

        $crumbs = [
            [
                'label' => Craft::t('sprout-seo', 'Redirects'),
                'url' => UrlHelper::cpUrl('redirects')
            ]
        ];

        $tabs = [
            [
                'label' => 'Redirect',
                'url' => '#tab1',
                'class' => null,
            ]
        ];

        return $this->renderTemplate('sprout-base-seo/redirects/_edit', [
            'currentSite' => $currentSite,
            'redirect' => $redirect,
            'methodOptions' => $methodOptions,
            'crumbs' => $crumbs,
            'tabs' => $tabs,
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
        $siteId = Craft::$app->getRequest()->getBodyParam('siteId');

        if ($redirectId) {
            $redirect = SproutSeo::$app->redirects->getRedirectById($redirectId);

            // todo - figure out how throw 404 errors let's assume that the redirectId exists
            //if (!$redirect) {
            //    throw new Exception(Craft::t('sprout-seo', 'No redirect exists with the ID “{id}”', [
            //        'id' => $redirectId
            //    ]));
            //}
            if (!$redirect){

                $redirect = new Redirect();
                $redirect->id = $redirectId;
            }
        } else {
            $redirect = new Redirect();
        }

        $defaultSiteId = Craft::$app->getSites()->getPrimarySite()->id;

        $oldUrl = Craft::$app->getRequest()->getRequiredBodyParam('oldUrl', $redirect->oldUrl);
        $newUrl = Craft::$app->getRequest()->getBodyParam('newUrl');

        // Set the event attributes, defaulting to the existing values for
        // whatever is missing from the post data
        $redirect->siteId = $siteId ?? $defaultSiteId;
        $redirect->oldUrl = $oldUrl;
        $redirect->newUrl = $newUrl;
        $redirect->method = Craft::$app->getRequest()->getRequiredBodyParam('method');
        $redirect->regex = Craft::$app->getRequest()->getBodyParam('regex');

        if (!$redirect->regex) {
            $redirect->regex = 0;
        }

        $redirect->enabled = Craft::$app->getRequest()->getBodyParam('enabled');

        if (!Craft::$app->elements->saveElement($redirect, true, false)) {
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
