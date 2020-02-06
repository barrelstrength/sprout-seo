<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\schema;

use barrelstrength\sproutseo\base\Schema;


class WebsiteIdentityOrganizationSchema extends Schema
{
    /**
     * @var
     */
    protected $type = 'Organization';

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Organization';
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isUnlistedSchemaType(): bool
    {
        return true;
    }

    /**
     * Does syntax user a generic `object` or do we need to assume
     * we know specifically what the variable is called?
     *
     * Have some out of box helper methods like getFirst()
     * Do we really need the @methodName syntax? or do we just write this in PHP?
     *
     * @return null|void
     * @throws \Exception
     */
    public function addProperties()
    {
        $schema = $this->globals['identity'];
        $socialProfiles = $this->globals['social'];

        $this->setOrganizationType($schema);

        $this->addText('name', $schema['name']);
        $this->addText('alternateName', $schema['alternateName']);
        $this->addText('description', $schema['description']);
        $this->addUrl('url', $schema['url']);
        $this->addTelephone('telephone', $schema['telephone']);
        $this->addEmail('email', $schema['email']);

        if (isset($schema['image'][0])) {
            $this->addImage('image', $schema['image'][0]);
        }

        // Add Corporate Contacts
        $contacts = $this->globals['contacts'];
        $this->addContactPoints($contacts);

        if (isset($schema['organizationSubTypes'][0]) && $schema['organizationSubTypes'][0] == 'LocalBusiness') {
            $openingHours = $schema['openingHours'] ?? null;

            $this->addOpeningHours($openingHours);
        }

        if (isset($schema['address']) && $schema['address']) {
            $this->addAddress('address');
        }

        if (isset($schema['foundingDate']['date'])) {
            $this->addDate('foundingDate', $schema['foundingDate']['date']);
        }

        if (isset($schema['priceRange'])) {
            $this->addText('priceRange', $schema['priceRange']);
        }

        if (is_array($socialProfiles) && count($socialProfiles)) {
            $urls = array_column($socialProfiles, 'url');
            $this->addSameAs($urls);
        }
    }

    /**
     * Process the selected Organization Type setting and update this schema type
     *
     * @param $schema
     */
    protected function setOrganizationType($schema)
    {
        $organization['organizationSubTypes'] = [];
        $organization['organizationSubTypes'][0] = $schema['organizationSubTypes'][0] ?? null;
        $organization['organizationSubTypes'][1] = $schema['organizationSubTypes'][1] ?? null;
        $organization['organizationSubTypes'][2] = $schema['organizationSubTypes'][2] ?? null;

        // Set the right value for @type
        foreach ($organization['organizationSubTypes'] as $org) {
            if ($org != '') {
                $this->type = $org;
            }
        }
    }
}