<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\models;


use barrelstrength\sproutbase\base\SproutSettingsInterface;
use barrelstrength\sproutseo\SproutSeo;
use craft\base\Model;
use Craft;

/**
 *
 * @property array $settingsNavItems
 */
class Settings extends Model implements SproutSettingsInterface
{
    /**
     * @var string
     */
    public $pluginNameOverride = '';

    /**
     * @var bool
     */
    public $enableGlobals = true;

    /**
     * @var bool
     */
    public $enableRedirects = true;

    /**
     * @var bool
     */
    public $enableSitemaps = true;

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
     * @var int
     */
    public $maxMetaDescriptionLength = 160;

    /**
     * @inheritdoc
     */
    public function getSettingsNavItems(): array
    {
        $settings = SproutSeo::getInstance()->getSettings();

        $navItems['general'] = [
            'label' => Craft::t('sprout-seo', 'General'),
            'url' => 'sprout-seo/settings/general',
            'selected' => 'general',
            'template' => 'sprout-seo/settings/general'
        ];

        if (Craft::$app->getUser()->checkPermission('sproutSeo-editRedirects') &&  $settings->enableRedirects) {
            $navItems['redirects'] = [
                'label' => Craft::t('sprout-seo', 'Redirects'),
                'url' => 'sprout-seo/settings/redirects',
                'selected' => 'redirects',
                'template' => 'sprout-base-redirects/settings/redirects'
            ];
        }

        if (Craft::$app->getUser()->checkPermission('sproutSeo-editSitemaps') &&  $settings->enableSitemaps) {
            $navItems['sitemaps'] = [
                'label' => Craft::t('sprout-seo', 'Sitemaps'),
                'url' => 'sprout-seo/settings/sitemaps',
                'selected' => 'sitemaps',
                'template' => 'sprout-base-sitemaps/settings/sitemaps'
            ];
        }

        $navItems['advanced'] = [
            'label' => Craft::t('sprout-seo', 'Advanced'),
            'url' => 'sprout-seo/settings/advanced',
            'selected' => 'advanced',
            'template' => 'sprout-seo/settings/advanced',
        ];

        return $navItems;
    }
}