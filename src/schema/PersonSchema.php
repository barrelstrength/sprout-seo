<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\schema;

use craft\elements\User;
use Exception;
use Throwable;

class PersonSchema extends ThingSchema
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Person';
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return 'Person';
    }

    /**
     * @return bool
     */
    public function isUnlistedSchemaType(): bool
    {
        return false;
    }

    /**
     * @return array|null|void
     * @throws Exception
     * @throws Throwable
     */
    public function addProperties()
    {
        if (($this->element !== null) && get_class($this->element) === User::class) {
            $this->addUserElementProperties();
        } else {
            parent::addProperties();
        }
    }

    public function addUserElementProperties()
    {
        /**
         * @var User $element
         */
        $element = $this->element;

        $name = null;

        if (method_exists($element, 'getFullName')) {
            $name = $element->getFullName();
        }

        if ($element->firstName !== null && $element->lastName !== null) {
            $this->addText('givenName', $element->firstName);
            $this->addText('familyName', $element->lastName);

            $name = $element->firstName.' '.$element->lastName;
        }

        $this->addText('name', $name);
        $this->addEmail('email', $element->email);
    }
}