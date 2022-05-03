<?php

namespace BarrelStrength\SproutSeo;

use BarrelStrength\Sprout\core\db\InstallHelper;
use BarrelStrength\Sprout\core\db\SproutPluginMigrationInterface;
use BarrelStrength\Sprout\core\db\SproutPluginMigrator;
use BarrelStrength\Sprout\core\editions\Edition;
use BarrelStrength\Sprout\core\modules\Modules;
use BarrelStrength\Sprout\fields\FieldsModule;
use BarrelStrength\Sprout\meta\MetaModule;
use BarrelStrength\Sprout\redirects\RedirectsModule;
use BarrelStrength\Sprout\sitemaps\SitemapsModule;
use BarrelStrength\Sprout\uris\UrisModule;
use Craft;
use craft\base\Plugin;
use craft\db\MigrationManager;
use craft\errors\MigrationException;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\UrlHelper;
use yii\base\Event;
use yii\base\InvalidConfigException;

class SproutSeo extends Plugin implements SproutPluginMigrationInterface
{
    public const EDITION_LITE = 'lite';
    public const EDITION_PRO = 'pro';

    public string $minVersionRequired = '4.6.8';

    public string $schemaVersion = '0.0.1';

    /**
     * @inheritDoc
     */
    public static function editions(): array
    {
        return [
            self::EDITION_LITE,
            self::EDITION_PRO,
        ];
    }

    public static function getSchemaDependencies(): array
    {
        return [
            MetaModule::class,
            RedirectsModule::class,
            SitemapsModule::class,
            FieldsModule::class,
            UrisModule::class,
        ];
    }

    /**
     * @throws InvalidConfigException
     */
    public function getMigrator(): MigrationManager
    {
        return SproutPluginMigrator::make($this);
    }

    public function init(): void
    {
        parent::init();

        Event::on(
            Modules::class,
            Modules::EVENT_REGISTER_SPROUT_AVAILABLE_MODULES,
            static function(RegisterComponentTypesEvent $event) {
                $event->types[] = MetaModule::class;
                $event->types[] = RedirectsModule::class;
                $event->types[] = SitemapsModule::class;
            }
        );

        $this->instantiateSproutModules();
        $this->grantModuleEditions();
    }

    protected function instantiateSproutModules(): void
    {
        MetaModule::isEnabled() && MetaModule::getInstance();
        RedirectsModule::isEnabled() && RedirectsModule::getInstance();
        SitemapsModule::isEnabled() && SitemapsModule::getInstance();
    }

    protected function grantModuleEditions(): void
    {
        if ($this->edition === self::EDITION_PRO) {
            MetaModule::isEnabled() && MetaModule::getInstance()->grantEdition(Edition::PRO);
            RedirectsModule::isEnabled() && RedirectsModule::getInstance()->grantEdition(Edition::PRO);
//            SitemapsModule::isEnabled() && SitemapsModule::getInstance()->grantEdition(Edition::PRO);
        }
    }

    /**
     * @throws MigrationException
     */
    protected function afterInstall(): void
    {
        InstallHelper::runInstallMigrations($this);

        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            return;
        }

        // Initialize report cp URLs
//        Reports::getInstance();

        // Redirect to welcome page
        $url = UrlHelper::cpUrl('sprout/welcome/meta');
        Craft::$app->getResponse()->redirect($url)->send();
    }

    /**
     * @throws MigrationException
     * @throws InvalidConfigException
     */
    protected function beforeUninstall(): void
    {
        InstallHelper::runUninstallMigrations($this);
    }
}
