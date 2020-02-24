<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\schema;

class PlaceSchema extends ThingSchema
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Place';
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return 'Place';
    }

    /**
     * @return bool
     */
    public function isUnlistedSchemaType(): bool
    {
        return false;
    }
}