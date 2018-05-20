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

use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

/**
 * Redirects controller
 */
class RedirectsController extends Controller
{
    /**
     * Edit a Redirect
     *
     * @param int|null      $redirectId The redirect's ID, if editing an existing redirect.
     * @param Redirect|null $redirect   The redirect send back by setRouteParams if any errors on saveRedirect
     *
     * @return \yii\web\Response
     * @throws HttpException
     * @throws \ReflectionException
     */
    public function actionEditRedirect(int $redirectId = null, Redirect $redirect = null)
    {
        $variables = [];
        $variables['redirectId'] = $redirectId ?? null;
        $variables['redirect'] = $redirect ?? null;
        $variables['methodOptions'] = SproutSeo::$app->redirects->getMethods();

        $variables['subTitle'] = Craft::t('sprout-seo', 'Create a new redirect');

        // Now let's set up the actual redirect
        if ($variables['redirect'] == null) {
            if ($variables['redirectId'] != null) {
                $variables['subTitle'] = Craft::t('sprout-seo', 'Edit redirect');
                $variables['redirect'] = SproutSeo::$app->redirects->getRedirectById($variables['redirectId']);

                if (!$variables['redirect']) {
                    throw new HttpException(404);
                }
            } else {
                $variables['redirect'] = new Redirect();
            }
        }

        $variables['continueEditingUrl'] = 'sprout-base-seo/redirects/{id}';

        $variables['crumbs'] = [
            [
                'label' => Craft::t('sprout-seo', 'Redirects'),
                'url' => UrlHelper::cpUrl('redirects')
            ]
        ];

        return $this->renderTemplate('sprout-base-seo/redirects/_edit', $variables);
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

        // Set the event attributes, defaulting to the existing values for
        // whatever is missing from the post data
        $redirect->oldUrl = Craft::$app->getRequest()->getBodyParam('oldUrl', $redirect->oldUrl);
        $redirect->newUrl = Craft::$app->getRequest()->getBodyParam('newUrl');
        $redirect->method = Craft::$app->getRequest()->getBodyParam('method');
        $redirect->regex = Craft::$app->getRequest()->getBodyParam('regex');
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
