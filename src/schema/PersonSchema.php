<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\schema;

use craft\elements\User;

class PersonSchema extends ThingSchema
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Person';
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'Person';
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
        if ($this->element !== null) {
            if (get_class($this->element) === User::class) {
                $this->addUserElementProperties();
            } else {
                parent::addProperties();
            }
        } else {
            parent::addProperties();
        }
    }

    public function addUserElementProperties()
    {
        $element = $this->element;

        $name = null;

        if (method_exists($element, 'getFullName')) {
            $name = $element->getFullName();
        }

        if (isset($element->firstName) && isset($element->lastName)) {
            $this->addText('givenName', $element->firstName);
            $this->addText('familyName', $element->lastName);

            $name = $element->firstName.' '.$element->lastName;
        }

        $this->addText('name', $name);
        $this->addEmail('email', $element->email);
    }
}