<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo;

use barrelstrength\sproutbase\base\BaseSproutTrait;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\SproutBaseHelper;
use barrelstrength\sproutbasefields\SproutBaseFieldsHelper;
use barrelstrength\sproutbaseredirects\models\Settings as RedirectsSettingsModel;
use barrelstrength\sproutbaseredirects\SproutBaseRedirects;
use barrelstrength\sproutbaseredirects\SproutBaseRedirectsHelper;
use barrelstrength\sproutbasesitemaps\models\Settings as SitemapsSettingsModel;
use barrelstrength\sproutbasesitemaps\SproutBaseSitemaps;
use barrelstrength\sproutbasesitemaps\SproutBaseSitemapsHelper;
use barrelstrength\sproutbaseuris\SproutBaseUrisHelper;
use barrelstrength\sproutseo\fields\ElementMetadata;
use barrelstrength\sproutseo\models\Settings;
use barrelstrength\sproutseo\services\App;
use barrelstrength\sproutseo\web\twig\Extension as SproutSeoTwigExtension;
use barrelstrength\sproutseo\web\twig\variables\SproutSeoVariable;
use Craft;
use craft\base\Plugin;
use craft\events\ExceptionEvent;
use craft\events\FieldLayoutEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterUserPermissionsEvent;
use craft\helpers\UrlHelper;
use craft\services\Fields;
use craft\services\UserPermissions;
use craft\web\ErrorHandler;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use yii\base\Event;
use yii\base\InvalidConfigException;

/**
 *
 * @property mixed $cpNavItem
 * @property array $cpUrlRules
 * @property array $userPermissions
 * @property null  $upgradeUrl
 * @property array $siteUrlRules
 */
class SproutSeo extends Plugin
{
    use BaseSproutTrait;

    const EDITION_LITE = 'lite';

    const EDITION_PRO = 'pro';

    /**
     * Enable use of SproutSeo::$app-> in place of Craft::$app->
     *
     * @var App
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
    public $schemaVersion = '4.3.0.3';

    /**
     * @var string
     */
    public $minVersionRequired = '3.4.2';

    /**
     * @inheritdoc
     */
    public static function editions(): array
    {
        return [
            self::EDITION_LITE,
            self::EDITION_PRO,
        ];
    }

    /**
     * @inheritdoc
     *
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        SproutBaseHelper::registerModule();
        SproutBaseFieldsHelper::registerModule();
        SproutBaseRedirectsHelper::registerModule();
        SproutBaseSitemapsHelper::registerModule();
        SproutBaseUrisHelper::registerModule();

        $this->setComponents([
            'app' => App::class
        ]);

        self::$app = $this->get('app');

        Craft::setAlias('@sproutseo', $this->getBasePath());

        /** @noinspection CascadingDirnameCallsInspection */
        Craft::setAlias('@sproutseolib', dirname(__DIR__).'/lib');

        // Add Twig Extensions
        Craft::$app->view->registerTwigExtension(new SproutSeoTwigExtension());

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, $this->getCpUrlRules());
        });

        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_SITE_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, $this->getSiteUrlRules());
        });

        Event::on(UserPermissions::class, UserPermissions::EVENT_REGISTER_PERMISSIONS, function(RegisterUserPermissionsEvent $event) {
            $event->permissions['Sprout SEO'] = $this->getUserPermissions();
        });

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, static function(Event $event) {
            $event->sender->set('sproutSeo', SproutSeoVariable::class);
        });

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = ElementMetadata::class;
        });

        Event::on(Fields::class, Fields::EVENT_AFTER_SAVE_FIELD_LAYOUT, static function(FieldLayoutEvent $event) {
            SproutSeo::$app->elementMetadata->resaveElementsAfterFieldLayoutIsSaved($event);
        });

        Event::on(ErrorHandler::class, ErrorHandler::EVENT_BEFORE_HANDLE_EXCEPTION, function(ExceptionEvent $event) {
            if ($this->is(self::EDITION_PRO)) {
                SproutBaseRedirects::$app->redirects->handleRedirectsOnException($event);
            }
        });
    }

    public function getCpNavItem()
    {
        $parent = parent::getCpNavItem();
        $settings = $this->getSettings();

        $isPro = $this->is(self::EDITION_PRO);

        // Allow user to override plugin name in sidebar
        if ($this->getSettings()->pluginNameOverride) {
            $parent['label'] = $this->getSettings()->pluginNameOverride;
        }

        if (Craft::$app->getUser()->checkPermission('sproutSeo-editGlobals') && $settings->enableGlobals) {
            $parent['subnav']['globals'] = [
                'label' => Craft::t('sprout-seo', 'Globals'),
                'url' => 'sprout-seo/globals'
            ];
        }

        if (Craft::$app->getUser()->checkPermission('sproutSeo-editRedirects') && $settings->enableRedirects && $isPro) {
            $parent['subnav']['redirects'] = [
                'label' => Craft::t('sprout-seo', 'Redirects'),
                'url' => 'sprout-seo/redirects'
            ];
        }

        if (Craft::$app->getUser()->checkPermission('sproutSeo-editSitemaps') && $settings->enableSitemaps && $isPro) {
            $parent['subnav']['sitemaps'] = [
                'label' => Craft::t('sprout-seo', 'Sitemaps'),
                'url' => 'sprout-seo/sitemaps'
            ];
        }

        if (Craft::$app->getUser()->getIsAdmin()) {
            $parent['subnav']['settings'] = [
                'label' => Craft::t('sprout-seo', 'Settings'),
                'url' => 'sprout-seo/settings'
            ];
        }

        return $parent;
    }

    /**
     * @inheritDoc
     */
    public function getUpgradeUrl()
    {
        if (!SproutBase::$app->settings->isEdition('sprout-seo', self::EDITION_PRO)) {
            return UrlHelper::cpUrl('sprout-seo/upgrade');
        }

        return null;
    }

    /**
     * @return array
     */
    public function getUserPermissions(): array
    {
        return [
            'sproutSeo-editGlobals' => [
                'label' => Craft::t('sprout-seo', 'Edit Globals')
            ],
            'sproutSeo-editRedirects' => [
                'label' => Craft::t('sprout-seo', 'Edit Redirects')
            ],
            'sproutSeo-editSitemaps' => [
                'label' => Craft::t('sprout-seo', 'Edit Sitemaps')
            ],
        ];
    }

    /**
     * @return Settings
     */
    protected function createSettingsModel(): Settings
    {
        return new Settings();
    }

    /**
     * @inheritDoc
     */
    protected function afterInstall()
    {
        // Redirect to welcome page
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            return;
        }

        Craft::$app->controller->redirect(UrlHelper::cpUrl('sprout-seo/welcome'))->send();
    }

    /**
     * @return array
     */
    private function getCpUrlRules(): array
    {
        $rules = [
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
        ];

        if ($this->is(self::EDITION_PRO)) {
            $rules = array_merge($rules, [
                // Sitemaps
                '<pluginHandle:sprout-seo>/sitemaps/edit/<sitemapSectionId:\d+>/<siteHandle:.*>' =>
                    'sprout-base-sitemaps/sitemaps/sitemap-edit-template',
                '<pluginHandle:sprout-seo>/sitemaps/new/<siteHandle:.*>' =>
                    'sprout-base-sitemaps/sitemaps/sitemap-edit-template',
                '<pluginHandle:sprout-seo>/sitemaps/<siteHandle:.*>' =>
                    'sprout-base-sitemaps/sitemaps/sitemap-index-template',
                '<pluginHandle:sprout-seo>/sitemaps' =>
                    'sprout-base-sitemaps/sitemaps/sitemap-index-template',

                // Redirects
                '<pluginHandle:sprout-seo>/redirects/edit/<redirectId:\d+>/<siteHandle:.*>' =>
                    'sprout-base-redirects/redirects/edit-redirect-template',
                '<pluginHandle:sprout-seo>/redirects/edit/<redirectId:\d+>' =>
                    'sprout-base-redirects/redirects/edit-redirect-template',
                '<pluginHandle:sprout-seo>/redirects/new/<siteHandle:.*>' =>
                    'sprout-base-redirects/redirects/edit-redirect-template',
                '<pluginHandle:sprout-seo>/redirects/new' =>
                    'sprout-base-redirects/redirects/edit-redirect-template',
                '<pluginHandle:sprout-seo>/redirects/<siteHandle:.*>' =>
                    'sprout-base-redirects/redirects/redirects-index-template',
                '<pluginHandle:sprout-seo>/redirects' =>
                    'sprout-base-redirects/redirects/redirects-index-template',

                // Settings
                'sprout-seo/settings/redirects' => [
                    'route' => 'sprout/settings/edit-settings',
                    'params' => [
                        'sproutBaseSettingsType' => RedirectsSettingsModel::class
                    ]
                ],
                'sprout-seo/settings/sitemaps' => [
                    'route' => 'sprout/settings/edit-settings',
                    'params' => [
                        'sproutBaseSettingsType' => SitemapsSettingsModel::class
                    ]
                ]
            ]);
        }

        // Make sure our general settings come last in the rules
        $rules = array_merge($rules, [
            // Settings
            '<pluginHandle:sprout-seo>/settings/<settingsSectionHandle:.*>' =>
                'sprout/settings/edit-settings',
            'sprout-seo/settings' =>
                'sprout/settings/edit-settings',
        ]);

        return $rules;
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
    private function getSiteUrlRules(): array
    {
        if ($this->is(self::EDITION_PRO)) {
            $settings = SproutBaseSitemaps::$app->sitemaps->getSitemapsSettings();
            if ($settings->enableDynamicSitemaps) {
                return [
                    'sitemap-<sitemapKey:.*>-<pageNumber:\d+>.xml' =>
                        'sprout-base-sitemaps/xml-sitemap/render-xml-sitemap',
                    'sitemap-?<sitemapKey:.*>.xml' =>
                        'sprout-base-sitemaps/xml-sitemap/render-xml-sitemap',
                ];
            }
        }

        return [];
    }
}
