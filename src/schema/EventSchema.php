<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\schema;

class EventSchema extends ThingSchema
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Event';
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'Event';
    }

    /**
     * @return bool
     */
    public function isUnlistedSchemaType()
    {
        return false;
    }
}