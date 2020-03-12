<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\models;


use barrelstrength\sproutbase\base\SproutSettingsInterface;
use barrelstrength\sproutseo\SproutSeo;
use Craft;
use craft\base\Model;

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
    public $appendTitleValue;

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
     * @deprecated
     *
     * This field is required on the Sprout SEO Settings model
     * for the migration m190415_000000_adds_sprout_redirects_migration
     * so that the structureId setting gets properly migrated.
     *
     * General usage of this setting has moved to the SproutBaseRedirects Settings model.
     */
    public $structureId;

    /**
     * @inheritdoc
     */
    public function getSettingsNavItems(): array
    {
        /** @var SproutSeo $plugin */
        $plugin = SproutSeo::getInstance();
        $isPro = $plugin->is(SproutSeo::EDITION_PRO);

        $navItems['general'] = [
            'label' => Craft::t('sprout-seo', 'General'),
            'url' => 'sprout-seo/settings/general',
            'selected' => 'general',
            'template' => 'sprout-seo/settings/general'
        ];

        if (Craft::$app->getUser()->checkPermission('sproutSeo-editRedirects') && $isPro) {
            $navItems['redirects'] = [
                'label' => Craft::t('sprout-seo', 'Redirects'),
                'url' => 'sprout-seo/settings/redirects',
                'selected' => 'redirects',
                'template' => 'sprout-base-redirects/settings/redirects'
            ];
        }

        if (Craft::$app->getUser()->checkPermission('sproutSeo-editSitemaps') && $isPro) {
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

        if (!$isPro) {
            $navItems['upgrade'] = [
                'label' => Craft::t('sprout-seo', 'Upgrade to PRO'),
                'url' => 'sprout-seo/settings/upgrade',
                'selected' => 'upgrade',
                'template' => 'sprout-seo/settings/upgrade'
            ];
        }

        return $navItems;
    }
}