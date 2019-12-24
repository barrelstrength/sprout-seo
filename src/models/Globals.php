<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\models;

use barrelstrength\sproutbasefields\models\Address;
use barrelstrength\sproutbasefields\SproutBaseFields;
use craft\base\Model;
use craft\helpers\Json;

/**
 *
 * @property null|string $websiteIdentityType
 */
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
     * @var array
     */
    public $meta;

    /**
     * @var array
     */
    public $identity;

    /**
     * @var array
     */
    public $ownership;

    /**
     * @var array
     */
    public $contacts;

    /**
     * @var array
     */
    public $social;

    /**
     * @var array
     */
    public $robots;

    /**
     * @var array
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
     * @var Address|null
     */
    public $addressModel = null;

    public function init()
    {
        if (isset($this->identity['address']) && $this->addressModel === null) {
            $addressModel = new Address();
            $addressModel->setAttributes($this->identity['address'], false);
            $this->addressModel = $addressModel;
        }
    }
    /**
     * Factory to return schema of any type
     *
     * @param string $target
     * @param string $format
     *
     * @return array|string
     */
    public function getGlobalByKey($target, $format = 'array')
    {
        if ($target) {
            $this->globalKey = $target;
        }

        $targetMethod = 'get'.ucfirst($target);

        $schema = $this->{$targetMethod}();

        if ($format === 'json') {
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
     * @return array
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

        if (is_array($contacts)) {
            /** @noinspection ForeachSourceInspection */
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

        if (is_array($profiles)) {
            /** @noinspection ForeachSourceInspection */
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
        $this->getGlobalByKey('identity');

        if (isset($this->identity['organizationSubTypes'][0]) && $this->identity['organizationSubTypes'][0] === 'LocalBusiness') {
            return true;
        }

        return false;
    }
}
