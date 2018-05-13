<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\schema;

use barrelstrength\sproutseo\base\Schema;

class PostalAddressSchema extends Schema
{
    public $addressCountry;
    public $addressLocality;
    public $addressRegion;
    public $postalCode;
    public $streetAddress;

    /**
     * @return string
     */
    public function getName()
    {
        return 'Postal Address';
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'PostalAddress';
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
        $this->addText('addressCountry', $this->addressCountry);
        $this->addText('addressLocality', $this->addressLocality);
        $this->addText('addressRegion', $this->addressRegion);
        $this->addText('postalCode', $this->postalCode);
        $this->addText('streetAddress', $this->streetAddress);
    }
}