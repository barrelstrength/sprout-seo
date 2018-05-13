<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\events;

use yii\base\Event;

class RegisterUrlEnabledSectionTypesEvent extends Event
{
    // Properties
    // =========================================================================

    public $urlEnabledSectionTypes = [];
}
