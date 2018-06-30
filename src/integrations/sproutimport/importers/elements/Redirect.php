<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace Craft;

use barrelstrength\sproutbase\app\import\base\ElementImporter;
use barrelstrength\sproutseo\elements\Redirect as RedirectElement;
use barrelstrength\sproutseo\SproutSeo;

class Redirect extends ElementImporter
{
    /**
     * @inheritdoc
     */
    public function getModelName()
    {
        return RedirectElement::class;
    }

    /**
     * @param $model
     *
     * @return null
     */
    public function getFieldLayoutId($model)
    {
        return null;
    }

    /**
     * @inheritdoc
     *
     * @throws \Throwable
     * @throws \yii\base\Exception
     */
    public function save()
    {
        return SproutSeo::$app->redirects->saveRedirect($this->model);
    }
}