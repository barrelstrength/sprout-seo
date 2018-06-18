<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo;

use barrelstrength\sproutbase\base\BaseSproutTrait;
use barrelstrength\sproutbase\SproutBaseHelper;
use barrelstrength\sproutforms\elements\Form;
use barrelstrength\sproutseo\events\RegisterSchemasEvent;
use barrelstrength\sproutseo\events\RegisterUrlEnabledSectionTypesEvent;
use barrelstrength\sproutseo\sectiontypes\Category;
use barrelstrength\sproutseo\sectiontypes\CommerceProduct;
use barrelstrength\sproutseo\sectiontypes\Entry;
use barrelstrength\sproutseo\fields\ElementMetadata;
use barrelstrength\sproutseo\models\Settings;
use barrelstrength\sproutseo\services\App;
use barrelstrength\sproutseo\services\Optimize;
use barrelstrength\sproutseo\services\Sitemaps;
use barrelstrength\sproutseo\services\UrlEnabledSections;
use Craft;
use craft\base\Plugin;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;
use barrelstrength\sproutseo\web\twig\variables\SproutSeoVariable;
use barrelstrength\sproutseo\web\twig\Extension as SproutSeoTwigExtension;
use craft\events\RegisterUrlRulesEvent;
use craft\web\twig\variables\CraftVariable;
use craft\web\ErrorHandler;
use craft\events\ExceptionEvent;
use yii\web\NotFoundHttpException;
use craft\web\UrlManager;
use yii\base\Event;
use barrelstrength\sproutbase\services\Settings as SproutBaseSettings;
use barrelstrength\sproutbase\events\BeforeSaveSettingsEvent;

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
    public $schemaVersion = '4.0.0';

    /**
     * @var string
     */
    public $minVersionRequired = '3.4.2';

    public function init()
    {
        parent::init();

        SproutBaseHelper::registerModule();

        $this->setComponents([
            'app' => App::class
        ]);

        self::$app = $this->get('app');

        Craft::setAlias('@sproutseolib', dirname(__DIR__, 2).'/sprout-seo/lib');

        // Add Twig Extensions
        Craft::$app->view->twig->addExtension(new SproutSeoTwigExtension());

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

        // Listen before Sprout SEO settings are saved
        Event::on(SproutBaseSettings::class, SproutBaseSettings::EVENT_BEFORE_SAVE_SETTINGS, function(BeforeSaveSettingsEvent $event) {
            if ($event->plugin->id == self::$pluginHandle) {
                //Craft::dd($event->settings);
                // @todo - copy default fields as urlEnabledSectionId and type
            }
        });

        // @todo - research craft()->isConsole() method was removed on craft3
        Event::on(ErrorHandler::class, ErrorHandler::EVENT_BEFORE_HANDLE_EXCEPTION, function(ExceptionEvent $event) {
                $exception = $event->exception;
                $request = Craft::$app->getRequest();
                if (get_class($exception) == NotFoundHttpException::class && $exception->statusCode == 404) {
                    if ($request->getIsSiteRequest() && !$request->getIsLivePreview()) {
                        $url = $request->getUrl();

                        // check if the request url needs redirect
                        $redirect = SproutSeo::$app->redirects->getRedirect($url);

                        $plugin = Craft::$app->plugins->getPlugin('sprout-seo');
                        $seoSettings = $plugin->getSettings();

                        if (!$redirect && $seoSettings->enable404RedirectLog) {
                            // Save new 404 Redirect
                            $redirect = SproutSeo::$app->redirects->save404Redirect($url);
                        }

                        if ($redirect) {
                            SproutSeo::$app->redirects->logRedirect($redirect->id);
                            // Use != instead of !== as 404 can be both as integer or string
                            if ($redirect->enabled && $redirect->method != 404) {
                                Craft::$app->getResponse()->redirect($redirect->newUrl, $redirect->method);
                                Craft::$app->end();
                            }
                        }
                    }
                }
            }
        );
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
                'sitemaps' => [
                    'label' => Craft::t('sprout-seo', 'Sitemaps'),
                    'url' => 'sprout-seo/sitemaps'
                ],
                'redirects' => [
                    'label' => Craft::t('sprout-seo', 'Redirects'),
                    'url' => 'sprout-seo/redirects'
                ],
                'settings' => [
                    'label' => Craft::t('sprout-seo', 'Settings'),
                    'url' => 'sprout-seo/settings'
                ],
            ]
        ]);
    }


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
                'template' => 'sprout-base-seo/index'
            ],

            'sprout-seo/globals' => [
                'template' => 'sprout-base-seo/globals/index'
            ],

            'sprout-seo/globals/<siteHandle:.*>/<globalHandle:.*>' =>
                'sprout-seo/global-metadata/edit-global-metadata',

            'sprout-seo/globals/<globalHandle:.*>' =>
                'sprout-seo/global-metadata/edit-global-metadata',

            'sprout-seo/sitemaps' =>
                'sprout-seo/sitemaps/sitemap-index-template',

            'sprout-seo/sitemaps/<siteHandle:.*>/new' =>
                'sprout-seo/sitemaps/sitemap-edit-template',

            'sprout-seo/sitemaps/<siteHandle:.*>/edit/<sitemapSectionId:\d+>' =>
                'sprout-seo/sitemaps/sitemap-edit-template',

            'sprout-seo/sitemaps/<siteHandle:.*>' =>
                'sprout-seo/sitemaps/sitemap-index-template',

            'sprout-seo/redirects/new' =>
                'sprout-seo/redirects/edit-redirect',

            'sprout-seo/redirects' =>
                'sprout-seo/redirects/redirects-index-template',

            'sprout-seo/redirects/<siteHandle:.*>/new' =>
                'sprout-seo/redirects/edit-redirect',

            'sprout-seo/redirects/<siteHandle:.*>/edit/<redirectId:\d+>' =>
                'sprout-seo/redirects/edit-redirect',

            'sprout-seo/redirects/<siteHandle:.*>' =>
                'sprout-seo/redirects/redirects-index-template',

            'sprout-seo/settings' =>
                'sprout-base/settings/edit-settings',

            'sprout-seo/settings/<settingsSectionHandle:.*>' =>
                'sprout-base/settings/edit-settings',

        ];
    }

    /**
     * Match dynamic sitemap URLs
     *
     * Example matches include:
     * - sitemap.xml
     * - singles-sitemap.xml
     * - custom-sections-sitemap.xml
     * - blog-entries-sitemap1.xml
     * - blog-entries-sitemap2.xml
     *
     * @return array
     */
    private function getSiteUrlRules()
    {
        $plugin = Craft::$app->plugins->getPlugin('sprout-seo');
        $seoSettings = $plugin->getSettings();

        if (isset($seoSettings->enableDynamicSitemaps) && $seoSettings->enableDynamicSitemaps) {
            return [
                '<section:.*>?sitemap<pageId:\d+>?.xml' =>
                    'sprout-seo/xml-sitemap/render-xml-sitemap'
            ];
        }

        return [];
    }
}
