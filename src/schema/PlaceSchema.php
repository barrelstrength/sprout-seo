<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\schema;

class PlaceSchema extends ThingSchema
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Place';
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'Place';
    }

    /**
     * @return bool
     */
    public function isUnlistedSchemaType()
    {
        return false;
    }
}