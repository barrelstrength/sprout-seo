<?php

namespace BarrelStrength\SproutSeo\migrations;

use BarrelStrength\Sprout\core\db\m000000_000000_sprout_plugin_migration;
use BarrelStrength\Sprout\core\db\SproutPluginMigrationInterface;
use BarrelStrength\SproutSeo\SproutSeo;

class m240318_053732_schema_4_44_444 extends m000000_000000_sprout_plugin_migration
{
    public function getPluginInstance(): SproutPluginMigrationInterface
    {
        return SproutSeo::getInstance();
    }
}