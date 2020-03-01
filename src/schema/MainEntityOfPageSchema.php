<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\schema;

use barrelstrength\sproutseo\base\Schema;

class MainEntityOfPageSchema extends Schema
{
    /**
     * @var
     */
    public $id;

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Main Entity Of Page';
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return 'WebPage';
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
        $this->addProperty('@id', $this->id);
    }
}