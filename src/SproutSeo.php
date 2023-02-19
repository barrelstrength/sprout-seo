<?php

namespace BarrelStrength\SproutSeo;

use BarrelStrength\Sprout\core\db\MigrationHelper;
use BarrelStrength\Sprout\core\db\SproutPluginMigrationInterface;
use BarrelStrength\Sprout\core\db\SproutPluginMigrator;
use BarrelStrength\Sprout\core\editions\Edition;
use BarrelStrength\Sprout\core\modules\Modules;
use BarrelStrength\Sprout\meta\MetaModule;
use BarrelStrength\Sprout\redirects\RedirectsModule;
use BarrelStrength\Sprout\sitemaps\SitemapsModule;
use BarrelStrength\Sprout\uris\UrisModule;
use Craft;
use craft\base\Plugin;
use craft\db\MigrationManager;
use craft\events\RegisterComponentTypesEvent;
use craft\helpers\UrlHelper;
use yii\base\Event;

class SproutSeo extends Plugin implements SproutPluginMigrationInterface
{
    public string $minVersionRequired = '4.6.8';

    public string $schemaVersion = '4.44.444';

    public static function editions(): array
    {
        return [
            Edition::LITE,
            Edition::PRO,
        ];
    }

    public static function getSchemaDependencies(): array
    {
        return [
            MetaModule::class,
            RedirectsModule::class,
            SitemapsModule::class,
            UrisModule::class,
        ];
    }

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
        if ($this->edition === Edition::PRO) {
            MetaModule::isEnabled() && MetaModule::getInstance()->grantEdition(Edition::PRO);
            RedirectsModule::isEnabled() && RedirectsModule::getInstance()->grantEdition(Edition::PRO);
            SitemapsModule::isEnabled() && SitemapsModule::getInstance()->grantEdition(Edition::PRO);
        }
    }

    protected function afterInstall(): void
    {
        MigrationHelper::runMigrations($this);

        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            return;
        }

        // Redirect to welcome page
        $url = UrlHelper::cpUrl('sprout/welcome/meta');
        Craft::$app->getResponse()->redirect($url)->send();
    }

    protected function beforeUninstall(): void
    {
        MigrationHelper::runUninstallMigrations($this);
    }
}
