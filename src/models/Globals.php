<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\models;

use craft\base\Model;
use craft\helpers\Json;

class Globals extends Model
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $siteId;

    /**
     * @var string
     */
    public $meta;

    /**
     * @var string
     */
    public $identity;

    /**
     * @var string
     */
    public $ownership;

    /**
     * @var string
     */
    public $contacts;

    /**
     * @var string
     */
    public $social;

    /**
     * @var string
     */
    public $robots;

    /**
     * @var string
     */
    public $settings;

    /**
     * @var \DateTime
     */
    public $dateCreated;

    /**
     * @var \DateTime
     */
    public $dateUpdated;

    /**
     * @var int
     */
    public $uid;

    /**
     * @var string
     */
    public $globalKey;

    /**
     * Factory to return schema of any type
     *
     * @param        $target
     * @param string $format
     *
     * @return string
     */
    public function getGlobalByKey($target, $format = 'array')
    {
        if ($target) {
            $this->globalKey = $target;
        }

        $targetMethod = 'get'.ucfirst($target);

        $schema = $this->{$targetMethod}();

        if ($format == 'json') {
            return Json::encode($schema);
        }

        return $schema;
    }

    /**
     * @return null|string
     */
    public function getWebsiteIdentityType()
    {
        $this->getGlobalByKey('identity');
        $identityType = 'Organization';

        if (isset($this->identity['@type']) && $this->identity['@type'] != '') {
            $identityType = $this->identity['@type'];
        }

        return $identityType;
    }

    /**
     * @return mixed
     */
    protected function getMeta()
    {
        return $this->meta;
    }

    /**
     * Get the values associated with the Identity column in the database
     *
     * @return array
     */
    protected function getIdentity()
    {
        return $this->{$this->globalKey};
    }

    /**
     * Get the values associated with the Contacts column in the database
     *
     * @return array
     */
    protected function getContacts()
    {
        $contacts = $this->{$this->globalKey};

        $contactPoints = [];

        if (count($contacts)) {
            foreach ($contacts as $contact) {
                $contactPoints[] = [
                    '@type' => 'ContactPoint',
                    'contactType' => $contact['contactType'] ?? $contact[0],
                    'telephone' => $contact['telephone'] ?? $contact[1]
                ];
            }
        }

        return $contactPoints;
    }

    /**
     * Get the values associated with the Social column in the database
     *
     * @return array
     */
    protected function getSocial()
    {
        $profiles = $this->{$this->globalKey};

        $profileLinks = [];

        if (count($profiles)) {
            foreach ($profiles as $profile) {
                $profileLinks[] = [
                    'profileName' => $profile['profileName'] ?? $profile[0],
                    'url' => $profile['url'] ?? $profile[1]
                ];
            }
        }

        return $profileLinks;
    }

    /**
     * Get the values associated with the Ownership column in the database
     *
     * @return array
     */
    protected function getOwnership()
    {
        return $this->{$this->globalKey};
    }

    /**
     * Get the values associated with the Robots column in the database
     *
     * @return array
     */
    protected function getRobots()
    {
        return $this->{$this->globalKey};
    }

    /**
     * Get the values associated with the Settings column in the database
     *
     * @return array
     */
    protected function getSettings()
    {
        return $this->{$this->globalKey};
    }

    /**
     * Determine if the selected Website Identity Schema Type is a Local Business
     *
     * @return null|string
     */
    public function isLocalBusiness()
    {
        $identity = $this->getGlobalByKey('identity');

        if (isset($identity['organizationSubTypes'][0]) and $identity['organizationSubTypes'][0] == 'LocalBusiness') {
            return true;
        }

        return false;
    }
}
