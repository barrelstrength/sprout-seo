<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\meta;

use barrelstrength\sproutseo\base\MetaType;
use Craft;

/**
 * Implements all attributes used in schema metadata
 */
class SchemaMetaType extends MetaType
{
    /**
     * @var int|null
     */
    protected $schemaTypeId;

    /**
     * @var int|null
     */
    protected $schemaOverrideTypeId;

    public static function displayName(): string
    {
        return Craft::t('sprout-seo', 'Schema');
    }

    public function attributes(): array
    {
        $attributes = parent::attributes();
        $attributes[] = 'schemaTypeId';
        $attributes[] = 'schemaOverrideTypeId';

        return $attributes;
    }

    public function getHandle(): string
    {
        return 'schema';
    }

    public function hasMetaDetails(): bool
    {
        return false;
    }

    /**
     * @return int|null
     */
    public function getSchemaTypeId()
    {
        return $this->schemaTypeId;
    }

    /**
     * @param $value
     */
    public function setSchemaTypeId($value)
    {
        $this->schemaTypeId = $value;
    }

    /**
     * @return int|null
     */
    public function getSchemaOverrideTypeId()
    {
        // Make sure our schema type and override are all or nothing
        // if we find the $metadataModel doesn't have a value for schemaOverrideTypeId
        // then we should make sure the $prioritizedMetadataModel also has a null value
        // otherwise we still keep our lower level value
//        if ($this->schemaTypeId !== null && $this->schemaOverrideTypeId == null) {
//            $prioritizedMetadataModel->{$attribute} = null;
//        }

        return $this->schemaOverrideTypeId;
    }

    /**
     * @param $value
     */
    public function setSchemaOverrideTypeId($value)
    {
        $this->schemaOverrideTypeId = $value;
    }
}
