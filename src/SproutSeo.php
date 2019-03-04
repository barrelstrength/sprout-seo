<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo;

use barrelstrength\sproutbase\base\BaseSproutTrait;
use barrelstrength\sproutbase\SproutBaseHelper;
use barrelstrength\sproutbasefields\SproutBaseFieldsHelper;
use barrelstrength\sproutseo\fields\ElementMetadata;
use barrelstrength\sproutseo\models\Settings;
use barrelstrength\sproutseo\services\App;
use barrelstrength\sproutseo\web\twig\variables\SproutSeoVariable;
use barrelstrength\sproutseo\web\twig\Extension as SproutSeoTwigExtension;

use Craft;
use craft\base\Plugin;
use craft\events\FieldLayoutEvent;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use yii\base\Event;

/**
 *
 * @property mixed $cpNavItem
 * @property array $cpUrlRules
 * @property array $siteUrlRules
 */
class SproutSeo extends Plugin
{
    use BaseSproutTrait;

    /**
     * Enable use of SproutSeo::$app-> in place of Craft::$app->
     *
     * @var \barrelstrength\sproutseo\services\App
     */
    public static $app;

    /**
     * Identify our plugin for BaseSproutTrait
     *
     * @var string
     */
    public static $pluginHandle = 'sprout-seo';

    /**
     * @var bool
     */
    public $hasCpSection = true;

    /**
     * @var string
     */
    public $schemaVersion = '4.0.4';

    /**
     * @var string
     */
    public $minVersionRequired = '3.4.2';

    /**
     * @inheritdoc
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();

        SproutBaseHelper::registerModule();
        SproutBaseFieldsHelper::registerModule();

        $this->setComponents([
            'app' => App::class
        ]);

        self::$app = $this->get('app');

        Craft::setAlias('@sproutseo', $this->getBasePath());

        /** @noinspection CascadingDirnameCallsInspection */
        Craft::setAlias('@sproutseolib', dirname(__DIR__, 1).'/lib');

        // Add Twig Extensions
        Craft::$app->view->registerTwigExtension(new SproutSeoTwigExtension());

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, $this->getCpUrlRules());
        });

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_SITE_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, $this->getSiteUrlRules());
        });

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function(RegisterComponentTypesEvent $event) {
            $event->types[] = ElementMetadata::class;
        });

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            $variable = $event->sender;
            $variable->set('sproutSeo', SproutSeoVariable::class);
        });

        Event::on(Fields::class, Fields::EVENT_AFTER_SAVE_FIELD_LAYOUT, function(FieldLayoutEvent $event) {
            SproutSeo::$app->elementMetadata->resaveElementsAfterFieldLayoutIsSaved($event);
        });
    }

    public function getCpNavItem()
    {
        $parent = parent::getCpNavItem();

        // Allow user to override plugin name in sidebar
        if ($this->getSettings()->pluginNameOverride) {
            $parent['label'] = $this->getSettings()->pluginNameOverride;
        }

        return array_merge($parent, [
            'subnav' => [
                'globals' => [
                    'label' => Craft::t('sprout-seo', 'Globals'),
                    'url' => 'sprout-seo/globals'
                ],
                'settings' => [
                    'label' => Craft::t('sprout-seo', 'Settings'),
                    'url' => 'sprout-seo/settings'
                ],
            ]
        ]);
    }

    /**
     * @return Settings
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @return array
     */
    private function getCpUrlRules()
    {
        return [
            'sprout-seo' => [
                'template' => 'sprout-seo/index'
            ],

            // Globals
            'sprout-seo/globals/<selectedTabHandle:.*>/<siteHandle:.*>' =>
                'sprout-seo/global-metadata/edit-global-metadata',

            'sprout-seo/globals/<selectedTabHandle:.*>' =>
                'sprout-seo/global-metadata/edit-global-metadata',

            'sprout-seo/globals' => [
                'template' => 'sprout-seo/globals/index'
            ],

            // Settings
            'sprout-seo/settings/<settingsSectionHandle:.*>' =>
                'sprout/settings/edit-settings',

            'sprout-seo/settings' =>
                'sprout/settings/edit-settings',
        ];
    }

    /**
     * Match dynamic sitemap URLs
     *
     * Example matches include:
     *
     * Sitemap Index Page
     * - sitemap.xml
     *
     * URL-Enabled Sections
     * - sitemap-t6PLT5o43IFG-1.xml
     * - sitemap-t6PLT5o43IFG-2.xml
     *
     * Special Groupings
     * - sitemap-singles.xml
     * - sitemap-custom-pages.xml
     *
     * @return array
     */
    private function getSiteUrlRules()
    {
        if ($this->getSettings()->enableDynamicSitemaps) {
            return [
                'sitemap-<sitemapKey:.*>-<pageNumber:\d+>.xml' =>
                    'sprout-seo/xml-sitemap/render-xml-sitemap',
                'sitemap-?<sitemapKey:.*>.xml' =>
                    'sprout-seo/xml-sitemap/render-xml-sitemap',
            ];
        }

        return [];
    }
}
