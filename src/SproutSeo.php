<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo;

use barrelstrength\sproutbase\app\seo\fields\ElementMetadata;
use barrelstrength\sproutbase\app\seo\web\twig\variables\SproutSeoVariable;
use barrelstrength\sproutbase\config\base\SproutBasePlugin;
use barrelstrength\sproutbase\config\configs\ControlPanelConfig;
use barrelstrength\sproutbase\config\configs\FieldsConfig;
use barrelstrength\sproutbase\config\configs\SeoConfig;
use barrelstrength\sproutbase\config\configs\RedirectsConfig;
use barrelstrength\sproutbase\config\configs\SitemapsConfig;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\SproutBaseHelper;
use Craft;
use craft\events\ExceptionEvent;
use craft\events\FieldLayoutEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\SiteEvent;
use craft\helpers\UrlHelper;
use craft\services\Fields;
use craft\services\Sites;
use craft\web\ErrorHandler;
use yii\base\Event;

/**
 * Class SproutSeo
 *
 * @package barrelstrength\sproutseo
 *
 * @property mixed $cpNavItem
 * @property array $cpUrlRules
 * @property null $upgradeUrl
 * @property array $userPermissions
 * @property array $sproutDependencies
 * @property array $siteUrlRules
 */
class SproutSeo extends SproutBasePlugin
{
    const EDITION_LITE = 'lite';
    const EDITION_PRO = 'pro';

    /**
     * @var string
     */
    public $schemaVersion = '4.5.0';

    /**
     * @var string
     */
    public $minVersionRequired = '4.6.3';

    public static function editions(): array
    {
        return [
            self::EDITION_LITE,
            self::EDITION_PRO,
        ];
    }

    public static function getSproutConfigs(): array
    {
        return [
            SeoConfig::class,
            FieldsConfig::class,
            RedirectsConfig::class,
            SitemapsConfig::class
        ];
    }

    public function init()
    {
        parent::init();

        SproutBaseHelper::registerModule();

        // Add Twig Extensions
//        Craft::$app->view->registerTwigExtension(new SproutSeoTwigExtension());

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = ElementMetadata::class;
        });

        Event::on(Fields::class, Fields::EVENT_AFTER_SAVE_FIELD_LAYOUT, static function(FieldLayoutEvent $event) {
            SproutBase::$app->elementMetadata->resaveElementsAfterFieldLayoutIsSaved($event);
        });

        Event::on(Sites::class, Sites::EVENT_AFTER_SAVE_SITE, static function(SiteEvent $event) {
            if ($event->isNew) {
                SproutBase::$app->globalMetadata->insertDefaultGlobalMetadata($event->site->id);
            }
        });
    }

    protected function afterInstall()
    {
        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            return;
        }

        // Redirect to welcome page
        $url = UrlHelper::cpUrl('sprout/welcome/seo');
        Craft::$app->controller->redirect($url)->send();
    }
}
