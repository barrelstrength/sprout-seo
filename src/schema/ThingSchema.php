<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\schema;

use barrelstrength\sproutseo\base\Schema;

class ThingSchema extends Schema
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Thing';
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return 'Thing';
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
        $metadata = $this->prioritizedMetadataModel;
        if ($this->isMainEntity) {
            $this->addMainEntityOfPage();
        }

        $this->addText('name', $metadata->optimizedTitle);
        $this->addText('description', $metadata->optimizedDescription);
        $this->addImage('image', $metadata->optimizedImage);
        $this->addUrl('url', $metadata->canonical);
    }
}