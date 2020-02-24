<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\schema;

use barrelstrength\sproutseo\base\Schema;
use Craft;

class WebsiteIdentityPersonSchema extends Schema
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
        return true;
    }

    /**
     * @return null|void
     * @throws \Exception
     */
    public function addProperties()
    {
        $schema = $this->globals['identity'];
        $socialProfiles = $this->globals['social'];

        $this->addText('name', $schema['name']);
        $this->addText('alternateName', $schema['alternateName']);
        $this->addText('description', $schema['description']);
        $this->addUrl('url', Craft::$app->sites->getCurrentSite()->getBaseUrl());
        $this->addTelephone('telephone', $schema['telephone']);
        $this->addEmail('email', $schema['email']);

        if (isset($schema['address']) && $schema['address']) {
            $this->addAddress('address');
        }

        if (isset($schema['image'][0])) {
            $this->addImage('image', $schema['image'][0]);
        }

        $contacts = $this->globals['contacts'];
        $this->addContactPoints($contacts);

        $this->addText('gender', $schema['gender']);

        if (is_array($socialProfiles) && count($socialProfiles)) {
            $urls = array_column($socialProfiles, 'url');
            $this->addSameAs($urls);
        }
    }
}