<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\events;

use yii\base\Event;

class RegisterSchemasEvent extends Event
{
    // Properties
    // =========================================================================

    public $schemas = [];
}
