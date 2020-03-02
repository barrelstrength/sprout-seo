<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\controllers;

use barrelstrength\sproutseo\SproutSeo;
use Craft;
use craft\base\Element;
use craft\web\Controller;
use craft\web\View;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class SeoPreviewController extends Controller
{
    /**
     * @param int $elementId
     * @param int $siteId
     *
     * @return Response
     * @throws Exception
     * @throws ForbiddenHttpException
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Throwable
     */
    public function actionPreview(int $elementId, int $siteId): Response
    {
        // Protect the preview target from public access:
        $this->requireAuthorization('sproutSeoPreviewAuthorizationKey:' . $elementId);

        // Start fresh
        SproutSeo::$app->optimize->element = null;

        /** @var Element $element */
        $element = Craft::$app->getElements()->getElementById($elementId, null, $siteId);

        if ($element) {
            SproutSeo::$app->optimize->element = $element;
        }

        $site = Craft::$app->getSites()->getSiteById($siteId);

        $metadata = SproutSeo::$app->optimize->getMetadata($site, false);

        $html = Craft::$app->getView()->renderTemplate('sprout-seo/_preview/preview', [
            'element' => $element,
            'metadata' => $metadata
        ], View::TEMPLATE_MODE_CP);

        return $this->asRaw($html);
    }
}
