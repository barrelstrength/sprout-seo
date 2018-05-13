<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
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
     * @var SectionMetadata
     */
    public $sectionMetadata;

    /**
     * @var ElementMetadata
     */
    public $elementMetadata;

    /**
     * @var Schema
     */
    public $schema;

    /**
     * @var Sitemap
     */
    public $sitemap;

    /**
     * @var Redirects
     */
    public $redirects;

    /**
     * @var Settings
     */
    public $settings;

    public function init()
    {
        $this->optimize = new Optimize();
        $this->globalMetadata = new GlobalMetadata();
        $this->sectionMetadata = new SectionMetadata();
        $this->elementMetadata = new ElementMetadata();
        $this->schema = new Schema();
        $this->sitemap = new Sitemap();
        $this->redirects = new Redirects();
        $this->settings = new Settings();
    }
}