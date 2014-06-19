<?php
namespace Craft;

require_once( dirname(__FILE__) . "/helpers/BSDPluginHelper.php" );

class SproutSeoPlugin extends BasePlugin
{
	public function getName()
	{
		$pluginName = Craft::t('Sprout SEO');

		return BSDPluginHelper::getPluginName($this, $pluginName);
	}

	public function getVersion()
	{
		return '0.6.3';
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
		// We are managing our settings on the CP Tab but storing them
		// in the plugin table so in order to use getSettings() we need
		// these defined here (I think)
		return array(
			'pluginNameOverride'  => AttributeType::String,
			'appendSiteName'      => AttributeType::Bool,
			'customGlobalValue'   => AttributeType::String,
			'seoDivider'          => AttributeType::String,
		);
	}

	/**
	 * Register control panel routes
	 */
	public function registerCpRoutes()
	{
		return array(
			'sproutseo/templates/new' =>
			'sproutseo/templates/_edit',

			'sproutseo/templates/(?P<templateId>\d+)' =>
			'sproutseo/templates/_edit',

			'sproutseo/sitemap/newPage' =>
			'sproutseo/sitemap/_edit',

			'sproutseo/settings' =>
			array('action' => 'sproutSeo/settings/settingsIndex')
		);
	}
}
