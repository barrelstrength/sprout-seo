<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\schema;

class IntangibleSchema extends ThingSchema
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Intangible';
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'Intangible';
    }

    /**
     * @return bool
     */
    public function isUnlistedSchemaType()
    {
        return false;
    }

    /**
     * @return array|null|void
     * @throws \Exception
     */
    public function addProperties()
    {
        parent::addProperties();
    }
}