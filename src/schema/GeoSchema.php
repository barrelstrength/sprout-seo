<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\schema;

use barrelstrength\sproutseo\base\Schema;

class GeoSchema extends Schema
{
    public $latitude;

    public $longitude;

    /**
     * @return string
     */
    public function getName()
    {
        return 'Geo';
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'GeoCoordinates';
    }

    /**
     * @return bool
     */
    public function isUnlistedSchemaType()
    {
        return true;
    }

    /**
     * @return null|void
     */
    public function addProperties()
    {
        $this->addText('latitude', $this->latitude);
        $this->addText('longitude', $this->longitude);
    }
}