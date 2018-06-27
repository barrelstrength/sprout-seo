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
    public $pluginNameOverride = '';
    public $appendTitleValue = false;
    public $displayFieldHandles = false;
    public $enableCustomSections = false;
    public $enableMetadataRendering = true;
    public $toggleMetadataVariable = false;
    public $metadataVariable = 'metadata';
    public $structureId = '';
    public $enable404RedirectLog = false;
    public $totalElementsPerSitemap = 500;
    public $enableDynamicSitemaps = true;
    public $total404Redirects = 500;
    public $maxMetaDescriptionLength = 160;
    public $enableMultilingualSitemaps = false;
    public $siteSettings = [];
    public $groupSettings = [];
    public $multilingualGroups;

    // @todo - do we still need this setting? Or should we rename to site nomenclature?
    public $localeIdOverride;

    public function getSettingsNavItems()
    {
        return [
            'overview' => [
                'label' => Craft::t('sprout-seo', 'Overview'),
                'url' => 'sprout-seo/settings/overview',
                'selected' => 'overview',
                'template' => 'sprout-base-seo/settings/overview'
            ],
            'settingsHeading' => [
                'heading' => Craft::t('sprout-seo', 'Settings'),
            ],
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
            'advanced' => [
                'label' => Craft::t('sprout-seo', 'Advanced'),
                'url' => 'sprout-seo/settings/advanced',
                'selected' => 'advanced',
                'template' => 'sprout-base-seo/settings/advanced',
            ],
            'integrationsHeading' => [
                'heading' => Craft::t('sprout-seo', 'Integrations'),
            ],
            'craftcommerce' => [
                'label' => Craft::t('sprout-seo', 'Craft Commerce'),
                'url' => 'sprout-seo/settings/craftcommerce',
                'selected' => 'craftcommerce',
                'template' => 'sprout-base-seo/_integrations/craftcommerce',
                'settingsForm' => false
            ],
            'sproutemail' => [
                'label' => Craft::t('sprout-seo', 'Sprout Email'),
                'url' => 'sprout-seo/settings/sproutemail',
                'selected' => 'sproutemail',
                'template' => 'sprout-base-seo/_integrations/sproutemail',
                'settingsForm' => false
            ],
            'sproutimport' => [
                'label' => Craft::t('sprout-seo', 'Sprout Import'),
                'url' => 'sprout-seo/settings/sproutimport',
                'selected' => 'sproutimport',
                'template' => 'sprout-base-seo/_integrations/sproutimport',
                'settingsForm' => false
            ]
        ];
    }
}