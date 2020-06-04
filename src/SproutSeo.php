<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo;

use barrelstrength\sproutbase\config\base\SproutCentralInterface;
use barrelstrength\sproutbase\config\configs\FieldsConfig;
use barrelstrength\sproutbase\config\configs\GeneralConfig;
use barrelstrength\sproutbase\config\configs\MetadataConfig;
use barrelstrength\sproutbase\config\configs\RedirectsConfig;
use barrelstrength\sproutbase\config\configs\SitemapsConfig;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\SproutBaseHelper;
use barrelstrength\sproutbase\app\metadata\fields\ElementMetadata;
use barrelstrength\sproutbase\app\metadata\web\twig\Extension as SproutSeoTwigExtension;
use barrelstrength\sproutbase\app\metadata\web\twig\variables\SproutSeoVariable;
use Craft;
use craft\base\Plugin;
use craft\events\ExceptionEvent;
use craft\events\FieldLayoutEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\UrlHelper;
use craft\services\Fields;
use craft\web\ErrorHandler;
use craft\web\twig\variables\CraftVariable;
use yii\base\Event;
use yii\base\InvalidConfigException;

/**
 * Class SproutSeo
 *
 * @package barrelstrength\sproutseo
 *
 * @property mixed $cpNavItem
 * @property array $cpUrlRules
 * @property null  $upgradeUrl
 * @property array $userPermissions
 * @property array $sproutDependencies
 * @property array $siteUrlRules
 */
class SproutSeo extends Plugin implements SproutCentralInterface
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

    public static function getSproutConfigs(): array
    {
        return [
            GeneralConfig::class,
            MetadataConfig::class,
            FieldsConfig::class,
            RedirectsConfig::class,
            SitemapsConfig::class
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

        Craft::setAlias('@sproutseo', $this->getBasePath());

        // Add Twig Extensions
        Craft::$app->view->registerTwigExtension(new SproutSeoTwigExtension());

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, static function(Event $event) {
            $event->sender->set('sproutSeo', SproutSeoVariable::class);
        });

        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, static function(RegisterComponentTypesEvent $event) {
            $event->types[] = ElementMetadata::class;
        });

        Event::on(Fields::class, Fields::EVENT_AFTER_SAVE_FIELD_LAYOUT, static function(FieldLayoutEvent $event) {
            SproutBase::$app->elementMetadata->resaveElementsAfterFieldLayoutIsSaved($event);
        });

        Event::on(ErrorHandler::class, ErrorHandler::EVENT_BEFORE_HANDLE_EXCEPTION, function(ExceptionEvent $event) {
            if ($this->is(self::EDITION_PRO)) {
                SproutBase::$app->redirects->handleRedirectsOnException($event);
            }
        });
    }

    /**
     * @inheritDoc
     */
    public function getUpgradeUrl()
    {
        if (!SproutBase::$app->config->isEdition('sprout-seo', self::EDITION_PRO)) {
            return UrlHelper::cpUrl('sprout-seo/upgrade');
        }

        return null;
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
}
