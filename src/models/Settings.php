<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\models;


use craft\base\Model;
use Craft;

class Settings extends Model
{
    /**
     * @var string
     */
    public $pluginNameOverride = '';

    /**
     * @var bool
     */
    public $appendTitleValue = false;

    /**
     * @var bool
     */
    public $displayFieldHandles = false;

    /**
     * @var bool
     */
    public $enableCustomSections = false;

    /**
     * @var bool
     */
    public $enableMetadataRendering = true;

    /**
     * @var bool
     */
    public $toggleMetadataVariable = false;

    /**
     * @var string
     */
    public $metadataVariable = 'metadata';

    /**
     * @var string
     */
    public $structureId = '';

    /**
     * @var bool
     */
    public $enable404RedirectLog = false;

    /**
     * @var int
     */
    public $totalElementsPerSitemap = 500;

    /**
     * @var bool
     */
    public $enableDynamicSitemaps = true;

    /**
     * @var int
     */
    public $total404Redirects = 500;

    /**
     * @var int
     */
    public $maxMetaDescriptionLength = 160;

    /**
     * @var bool
     */
    public $enableMultilingualSitemaps = false;

    /**
     * @var array
     */
    public $siteSettings = [];

    /**
     * @var array
     */
    public $groupSettings = [];

    /**
     * @return array
     */
    public function getSettingsNavItems()
    {
        return [
            'general' => [
                'label' => Craft::t('sprout-seo', 'General'),
                'url' => 'sprout-seo/settings/general',
                'selected' => 'general',
                'template' => 'sprout-base-seo/settings/general'
            ],
            'sitemaps' => [
                'label' => Craft::t('sprout-seo', 'Sitemaps'),
                'url' => 'sprout-seo/settings/sitemaps',
                'selected' => 'sitemaps',
                'template' => 'sprout-base-seo/settings/sitemaps'
            ],
            'redirects' => [
                'label' => Craft::t('sprout-seo', 'Redirects'),
                'url' => 'sprout-seo/settings/redirects',
                'selected' => 'redirects',
                'template' => 'sprout-base-seo/settings/redirects'
            ],
            'advanced' => [
                'label' => Craft::t('sprout-seo', 'Advanced'),
                'url' => 'sprout-seo/settings/advanced',
                'selected' => 'advanced',
                'template' => 'sprout-base-seo/settings/advanced',
            ]
        ];
    }
}