<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
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
    public function getName(): string
    {
        return 'Postal Address';
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return 'PostalAddress';
    }

    /**
     * @return bool
     */
    public function isUnlistedSchemaType(): bool
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