<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\services;

use craft\base\Component;

class App extends Component
{
    /**
     * @var Optimize
     */
    public $optimize;

    /**
     * @var GlobalMetadata
     */
    public $globalMetadata;

    /**
     * @var ElementMetadata
     */
    public $elementMetadata;

    /**
     * @var Schema
     */
    public $schema;

    /**
     * @var Settings
     */
    public $settings;

    public function init()
    {
        $this->optimize = new Optimize();
        $this->globalMetadata = new GlobalMetadata();
        $this->elementMetadata = new ElementMetadata();
        $this->schema = new Schema();
        $this->settings = new Settings();
    }
}