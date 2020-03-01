<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\events;

use yii\base\Event;

class RegisterSchemasEvent extends Event
{
    public $schemas = [];
}
