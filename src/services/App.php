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
     * @var ElementMetadata
     */
    public $elementMetadata;

    /**
     * @var Schema
     */
    public $schema;

    /**
     * @var Sitemaps
     */
    public $sitemaps;

    /**
     * @var XmlSitemap
     */
    public $xmlSitemap;

    /**
     * @var Redirects
     */
    public $redirects;

    /**
     * @var UrlEnabledSections
     */
    public $urlEnabledSections;

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
        $this->sitemaps = new Sitemaps();
        $this->xmlSitemap = new XmlSitemap();
        $this->redirects = new Redirects();
        $this->urlEnabledSections = new UrlEnabledSections();
        $this->settings = new Settings();
    }
}