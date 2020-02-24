<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\base;

trait SchemaTrait
{
    /**
     * @var string|null
     */
    protected $schemaTypeId;

    /**
     * @var string|null
     */
    protected $schemaOverrideTypeId;

    /**
     * @return string|null
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
     * @return string|null
     */
    public function getSchemaOverrideTypeId()
    {
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
