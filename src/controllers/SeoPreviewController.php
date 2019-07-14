<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\controllers;

use Craft;
use craft\base\Element;
use craft\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class SeoPreviewController extends Controller
{
    /**
     * @param int $elementId
     * @param int $siteId
     *
     * @return Response
     * @throws ForbiddenHttpException
     */
    public function actionPreview(int $elementId, int $siteId): Response
    {
        // Protect the preview target from public access:
        $this->requireAuthorization('customAuthorizationKey:' . $elementId);

        /** @var Element $element */
        $element = Craft::$app->getElements()->getElementById($elementId, null, $siteId);
        return $this->asRaw($element->title);
    }
}
