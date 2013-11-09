<?php
namespace Craft;

require_once( dirname(__FILE__) . "/helpers/BSDPluginHelper.php" );

class OneSeoPlugin extends BasePlugin
{
    public function getName()
    {
        // @TODO - make this into a function in a helper library
        // we will use it in several addons.

        // The plugin name
        $pluginName = Craft::t('One SEO');

        // @TODO - Can we find a way to move this to the BSDPluginHelper function?
        // I can't seem to pass an object or find the object we are working with.
        $pluginClassHandle = $this->getClassHandle(__CLASS__);

        return BSDPluginHelper::getPluginName($pluginName, $pluginClassHandle);
    }

    public function getVersion()
    {
        return '0.6.0';
    }

    public function getDeveloper()
    {
        return 'Barrel Strength Design';
    }

    public function getDeveloperUrl()
    {
        return 'http://barrelstrengthdesign.com';
    }

    public function hasCpSection()
    {
        return true;
    }

    protected function defineSettings()
    {
        return array(
            'pluginNameOverride'  => AttributeType::String,
            'appendSiteName'      => AttributeType::Bool,
            'customGlobalValue'   => AttributeType::String,
            'seoDivider'          => AttributeType::String,
        );
    }

    public function getSettingsHtml()
    {
        return craft()->templates->render('oneseo/_settings/settings', array(
            'settings' => $this->getSettings()
        ));
    }

    /**
     * Register control panel routes
     */
    public function hookRegisterCpRoutes()
    {
        return array(
            'oneseo\/fallbacks\/new' =>
            'oneseo/fallbacks/_edit',

            'oneseo\/fallbacks\/(?P<fallbackId>\d+)' =>
            'oneseo/fallbacks/_edit',
        );
    }

}

/*
Changelog

0.1.0   Initial Release

*/
