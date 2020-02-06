<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\schema;

use barrelstrength\sproutseo\base\Schema;

class WebsiteIdentityPlaceSchema extends Schema
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Place';
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return 'Place';
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
        $this->addUrl('url', $schema['url']);

        if (isset($schema['image'][0])) {
            $this->addImage('image', $schema['image'][0]);
        }

        $this->addTelephone('telephone', $schema['telephone']);

        if (isset($schema['address']) && $schema['address']) {
            $this->addAddress('address');
        }

        if (isset($schema['latitude'], $schema['longitude']) && $schema['latitude'] && $schema['longitude']) {
            $this->addGeo('geo', $schema['latitude'], $schema['longitude']);
        }

        if (is_array($socialProfiles) && count($socialProfiles)) {
            $urls = array_column($socialProfiles, 'url');
            $this->addSameAs($urls);
        }
    }
}