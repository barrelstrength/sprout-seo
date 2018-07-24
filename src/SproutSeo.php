<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo;

use barrelstrength\sproutbase\base\BaseSproutTrait;
use barrelstrength\sproutbase\SproutBaseHelper;
use barrelstrength\sproutseo\fields\ElementMetadata;
use barrelstrength\sproutseo\models\Settings;
use barrelstrength\sproutseo\services\App;
use barrelstrength\sproutseo\web\twig\variables\SproutSeoVariable;
use barrelstrength\sproutseo\web\twig\Extension as SproutSeoTwigExtension;


use Craft;
use craft\base\Plugin;
use craft\events\FieldLayoutEvent;
use craft\helpers\UrlHelper;
use craft\services\Fields;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\web\twig\variables\CraftVariable;
use craft\web\ErrorHandler;
use craft\events\ExceptionEvent;
use craft\web\UrlManager;
use yii\web\NotFoundHttpException;
use yii\base\Event;

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
    public $schemaVersion = '4.0.1';

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

        $this->setComponents([
            'app' => App::class
        ]);

        self::$app = $this->get('app');

        /** @noinspection CascadingDirnameCallsInspection */
        Craft::setAlias('@sproutseolib', dirname(__DIR__, 2).'/sprout-seo/lib');

        // Add Twig Extensions
        Craft::$app->view->twig->registerTwigExtension(new SproutSeoTwigExtension());

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

        Event::on(ErrorHandler::class, ErrorHandler::EVENT_BEFORE_HANDLE_EXCEPTION, function(ExceptionEvent $event) {

            $exception = $event->exception;
            $request = Craft::$app->getRequest();

            /**
             * @var NotFoundHttpException $exception
             */
            if (get_class($exception) === NotFoundHttpException::class &&
                $exception->statusCode === 404 &&
                $request->getIsSiteRequest() &&
                !$request->getIsLivePreview()
            ) {
                $currentSite = Craft::$app->getSites()->getCurrentSite();

                $uri = $request->getUrl();
                $absoluteUrl = $request->getAbsoluteUrl();

                // check if the request url needs redirect
                $redirect = SproutSeo::$app->redirects->findUrl($absoluteUrl, $currentSite);

                if (!$redirect && $this->getSettings()->enable404RedirectLog) {
                    // Save new 404 Redirect
                    $redirect = SproutSeo::$app->redirects->save404Redirect($absoluteUrl, $currentSite);
                }

                if ($redirect) {
                    SproutSeo::$app->redirects->logRedirect($redirect->id);
                    // Use != instead of !== as 404 can be both as integer or string
                    if ($redirect->enabled && $redirect->method !== 404) {
                        Craft::$app->getResponse()->redirect($redirect->getAbsoluteNewUrl(), $redirect->method);
                        Craft::$app->end();
                    }
                }
            }
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
                'template' => 'sprout-base-seo/index'
            ],

            // Globals
            'sprout-seo/globals/<selectedTabHandle:.*>/<siteHandle:.*>' =>
                'sprout-seo/global-metadata/edit-global-metadata',

            'sprout-seo/globals/<selectedTabHandle:.*>' =>
                'sprout-seo/global-metadata/edit-global-metadata',

            'sprout-seo/globals' => [
                'template' => 'sprout-base-seo/globals/index'
            ],

            // Sitemaps
            'sprout-seo/sitemaps/edit/<sitemapSectionId:\d+>/<siteHandle:.*>' =>
                'sprout-seo/sitemaps/sitemap-edit-template',

            'sprout-seo/sitemaps/new/<siteHandle:.*>' =>
                'sprout-seo/sitemaps/sitemap-edit-template',

            'sprout-seo/sitemaps/<siteHandle:.*>' =>
                'sprout-seo/sitemaps/sitemap-index-template',

            'sprout-seo/sitemaps' =>
                'sprout-seo/sitemaps/sitemap-index-template',

            // Redirects
            'sprout-seo/redirects/edit/<redirectId:\d+>/<siteHandle:.*>' =>
                'sprout-seo/redirects/edit-redirect',

            'sprout-seo/redirects/edit/<redirectId:\d+>' =>
                'sprout-seo/redirects/edit-redirect',

            'sprout-seo/redirects/new/<siteHandle:.*>' =>
                'sprout-seo/redirects/edit-redirect',

            'sprout-seo/redirects/new' =>
                'sprout-seo/redirects/edit-redirect',

            'sprout-seo/redirects/<siteHandle:.*>' =>
                'sprout-seo/redirects/redirects-index-template',

            'sprout-seo/redirects' =>
                'sprout-seo/redirects/redirects-index-template',

            // Settings
            'sprout-seo/settings/<settingsSectionHandle:.*>' =>
                'sprout-base/settings/edit-settings',

            'sprout-seo/settings' =>
                'sprout-base/settings/edit-settings',
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
