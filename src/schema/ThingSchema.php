<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\schema;

use barrelstrength\sproutseo\base\Schema;
use Throwable;
use yii\base\Exception;
use yii\base\InvalidConfigException;

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
     * @return void|null
     * @throws Throwable
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function addProperties()
    {
        $metadata = $this->prioritizedMetadataModel;
        if ($this->isMainEntity) {
            $this->addMainEntityOfPage();
        }

        $this->addText('name', $metadata->getOptimizedTitle());
        $this->addText('description', $metadata->getOptimizedDescription());
        $this->addImage('image', $metadata->getOptimizedImage());
        $this->addUrl('url', $metadata->getCanonical());
    }
}