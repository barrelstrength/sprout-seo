<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\enums;

/**
 * The Method class is an abstract class that defines the different methods available in one Redirect.
 */
abstract class RedirectMethods
{
    // Constants
    // =========================================================================

    const Permanent = 301;
    const Temporary = 302;
    const PageNotFound = 404;
}
