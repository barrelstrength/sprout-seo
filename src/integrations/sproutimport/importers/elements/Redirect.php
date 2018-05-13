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
     * @return mixed
     */
    public function getModelName()
    {
        return RedirectElement::class;
    }

    /**
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function save()
    {
        return SproutSeo::$app->redirects->saveRedirect($this->model);
    }
}