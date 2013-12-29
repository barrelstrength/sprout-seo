<?php
namespace Craft;

require_once( dirname(__FILE__) . "/helpers/BSDPluginHelper.php" );

class SproutSeoPlugin extends BasePlugin
{
    public function getName()
    {
        // @TODO - make this into a function in a helper library
        // we will use it in several addons.

        // The plugin name
        $pluginName = Craft::t('Sprout SEO');

        // @TODO - Can we find a way to move this to the BSDPluginHelper function?
        // I can't seem to pass an object or find the object we are working with.
        $pluginClassHandle = $this->getClassHandle(__CLASS__);

        return BSDPluginHelper::getPluginName($pluginName, $pluginClassHandle);
    }

    public function getVersion()
    {
        return '0.6.0.1';
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

    public function init()
    {
        craft()->on('entries.saveEntry', array($this, 'onSaveEntry'));
        craft()->on('content.saveContent', array($this, 'onSaveContent'));

        // Consider adding support for Global Pages
        // craft()->on('globals.saveGlobalContent', array($this, 'onSaveGlobalContent'));

        // Consider adding support for File Galleries, or are these handled in the 
        // section content?  Somebody could have a file gallery without a section
        // craft()->on('assets.saveFileContent', array($this, 'onSaveFileContent'));

    }

    public function onSaveEntry(Event $event)
    {
        // @TODO
        // Test and see if the Section Entry being saved belongs to 
        // a Section that we want to ping for.
        // Get Sitemap URL
        // Call ping function
    }

    public function onSaveContent(Event $event)
    {
        // @TODO
        // Test and see if the Section Entry being saved belongs to 
        // a Section that we want to ping for.
        // Get Sitemap
        // Call ping function
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
        return craft()->templates->render('sproutseo/_settings/settings', array(
            'settings' => $this->getSettings()
        ));
    }

    /**
     * Register control panel routes
     */
    public function hookRegisterCpRoutes()
    {
        return array(
            'sproutseo\/fallbacks\/new' =>
            'sproutseo/fallbacks/_edit',

            'sproutseo\/fallbacks\/(?P<fallbackId>\d+)' =>
            'sproutseo/fallbacks/_edit',
        );
    }

}

/*
Changelog

0.1.0   Initial Release

*/
