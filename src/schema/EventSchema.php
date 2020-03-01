<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\schema;

class EventSchema extends ThingSchema
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Event';
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return 'Event';
    }

    /**
     * @return bool
     */
    public function isUnlistedSchemaType(): bool
    {
        return false;
    }
}