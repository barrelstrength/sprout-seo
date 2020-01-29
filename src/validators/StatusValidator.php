<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\validators;

use barrelstrength\sproutseo\enums\RedirectStatuses;
use Craft;
use yii\validators\Validator;

class StatusValidator extends Validator
{
    /**
     * @inheritdoc
     */
    public function validateAttribute($object, $attribute)
    {
        if (!in_array($object->$attribute, [RedirectStatuses::ON, RedirectStatuses::OFF], true)) {
            $this->addError($object, $attribute, Craft::t('sprout-seo', 'The status must be either "ON" or "OFF".'));
        }
    }
}
