<?php
namespace Craft;

/**
 * Class SproutSeoPlugin
 *
 * @package Craft
 */
class SproutSeoPlugin extends BasePlugin
{
	/**
	 * @return string
	 */
	public function getName()
	{
		$pluginNameOverride = $this->getSettings()->getAttribute('pluginNameOverride');

		return empty($pluginNameOverride) ? Craft::t('Sprout SEO') : $pluginNameOverride;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return 'Content-focused SEO. Curate every detail of your online visibility.';
	}

	/**
	 * @return string
	 */
	public function getVersion()
	{
		return '2.4.1';
	}

	/**
	 * @return string
	 */
	public function getSchemaVersion()
	{
		return '2.4.0';
	}

	/**
	 * @return string
	 */
	public function getDeveloper()
	{
		return 'Barrel Strength Design';
	}

	/**
	 * @return string
	 */
	public function getDeveloperUrl()
	{
		return 'http://barrelstrengthdesign.com';
	}

	/**
	 * @return string
	 */
	public function getDocumentationUrl()
	{
		return 'http://sprout.barrelstrengthdesign.com/craft-plugins/seo/docs';
	}

	/**
	 * @return string
	 */
	public function getReleaseFeedUrl()
	{
		return 'https://sprout.barrelstrengthdesign.com/craft-plugins/seo/releases.json';
	}

	/**
	 * @return bool
	 */
	public function hasCpSection()
	{
		return true;
	}

	/**
	 * Get Settings URL
	 */
	public function getSettingsUrl()
	{
		return 'sproutseo/settings/general';
	}

	/* --------------------------------------------------------------
	 * HOOKS
	 * ------------------------------------------------------------ */

	public function init()
	{
		Craft::import('plugins.sproutseo.helpers.SproutSeoOptimizeHelper');

		Craft::import('plugins.sproutseo.contracts.SproutSeoBaseUrlEnabledSectionType');
		Craft::import('plugins.sproutseo.integrations.sproutseo.sectiontypes.SproutSeo_EntryUrlEnabledSectionType');
		Craft::import('plugins.sproutseo.integrations.sproutseo.sectiontypes.SproutSeo_CategoryUrlEnabledSectionType');
		Craft::import('plugins.sproutseo.integrations.sproutseo.sectiontypes.SproutSeo_CommerceProductUrlEnabledSectionType');

		Craft::import('plugins.sproutseo.contracts.SproutSeoBaseSchema');
		Craft::import('plugins.sproutseo.integrations.sproutseo.schema.SproutSeo_WebsiteIdentityOrganizationSchema');
		Craft::import('plugins.sproutseo.integrations.sproutseo.schema.SproutSeo_WebsiteIdentityPersonSchema');
		Craft::import('plugins.sproutseo.integrations.sproutseo.schema.SproutSeo_WebsiteIdentityWebsiteSchema');
		Craft::import('plugins.sproutseo.integrations.sproutseo.schema.SproutSeo_WebsiteIdentityPlaceSchema');
		Craft::import('plugins.sproutseo.integrations.sproutseo.schema.SproutSeo_ContactPointSchema');
		Craft::import('plugins.sproutseo.integrations.sproutseo.schema.SproutSeo_ImageObjectSchema');
		Craft::import('plugins.sproutseo.integrations.sproutseo.schema.SproutSeo_MainEntityOfPageSchema');

		Craft::import('plugins.sproutseo.integrations.sproutseo.schema.SproutSeo_ThingSchema');
		Craft::import('plugins.sproutseo.integrations.sproutseo.schema.SproutSeo_CreativeWorkSchema');
		Craft::import('plugins.sproutseo.integrations.sproutseo.schema.SproutSeo_EventSchema');
		Craft::import('plugins.sproutseo.integrations.sproutseo.schema.SproutSeo_IntangibleSchema');
		Craft::import('plugins.sproutseo.integrations.sproutseo.schema.SproutSeo_OrganizationSchema');
		Craft::import('plugins.sproutseo.integrations.sproutseo.schema.SproutSeo_PersonSchema');
		Craft::import('plugins.sproutseo.integrations.sproutseo.schema.SproutSeo_PlaceSchema');
		Craft::import('plugins.sproutseo.integrations.sproutseo.schema.SproutSeo_ProductSchema');

		Craft::import('plugins.sproutseo.integrations.sproutimport.SproutSeo_RedirectSproutImportElementImporter');

		if (!craft()->isConsole())
		{
			if (craft()->request->isSiteRequest() && !craft()->request->isLivePreview())
			{
				$url = craft()->request->getUrl();

				// check if the request url needs redirect
				$redirect = sproutSeo()->redirects->getRedirect($url);

				if ($redirect)
				{
					craft()->request->redirect($redirect->newUrl, true, $redirect->method);
				}
			}

			if (craft()->request->isCpRequest() && craft()->request->getSegment(1) == 'sproutseo')
			{
				craft()->templates->includeJsResource('sproutseo/js/sproutbase.js');
				craft()->templates->includeJs("new Craft.SproutBase({
					pluginName: 'Sprout SEO',
					pluginUrl: 'http://sprout.barrelstrengthdesign.com/craft-plugins/seo',
					pluginVersion: '" . $this->getVersion() . "',
					pluginDescription: '" . $this->getDescription() . "',
					developerName: '(Barrel Strength)',
					developerUrl: '" . $this->getDeveloperUrl() . "'
				})");
			}
		}
	}

	/**
	 * @return array
	 */
	protected function defineSettings()
	{
		// We are managing our settings on the CP Tab but storing them
		// in the plugin table so in order to use getSettings() we need
		// these defined here
		return array(
			'pluginNameOverride'      => AttributeType::String,
			'seoDivider'              => array(AttributeType::String, 'default' => '-'),
			'structureId'             => array(AttributeType::Number, 'default' => null),
			'twitterCard'             => array(AttributeType::String, 'default' => null),
			'ogType'                  => array(AttributeType::String, 'default' => null),
			'localeIdOverride'        => array(AttributeType::String, 'default' => null),
			'enableCustomSections'    => array(AttributeType::Bool, 'default' => false),
			'enableMetaDetailsFields' => array(AttributeType::Bool, 'default' => false),
			'enableMetadataRendering' => array(AttributeType::Bool, 'default' => true),
			'metadataVariable'        => array(AttributeType::String, 'default' => null),
		);
	}

	/**
	 * @return array
	 */
	public function registerCpRoutes()
	{
		return array(
			'sproutseo/sections/new'                          => array(
				'action' => 'sproutSeo/sectionMetadata/sectionMetadataEditTemplate'
			),
			'sproutseo/sections/(?P<sectionMetadataId>\d+)'   => array(
				'action' => 'sproutSeo/sectionMetadata/sectionMetadataEditTemplate'
			),
			'sproutseo/redirects'                             => array(
				'action' => 'sproutSeo/redirects/redirectIndex'
			),
			'sproutseo/redirects/new'                         => array(
				'action' => 'sproutSeo/redirects/editRedirect'
			),
			'sproutseo/redirects/(?P<redirectId>\d+)'         => array(
				'action' => 'sproutSeo/redirects/editRedirect'
			),
			'sproutseo/settings'                              => array(
				'action' => 'sproutSeo/settings/settingsIndex'
			),
			'sproutseo/settings/(?P<settingsTemplate>.*)/new' =>
				'sproutseo/settings/schema/_edit',

			'sproutseo/settings/(?P<settingsTemplate>.*)' => array(
				'action' => 'sproutSeo/settings/settingsIndex'
			),
		);
	}

	/**
	 * @return array
	 */
	public function registerUserPermissions()
	{
		return array(
			'editSproutSeoSettings' => array(
				'label' => Craft::t('Edit Settings')
			)
		);
	}

	/**
	 * Add any Twig extensions.
	 *
	 * @return mixed
	 */
	public function addTwigExtension()
	{
		Craft::import('plugins.sproutseo.twigextensions.SproutSeoTwigExtension');

		return new SproutSeoTwigExtension();
	}

	/**
	 * Register Sprout Import importers classes for the Sprout Import plugin integration
	 *
	 * @return array
	 */
	public function registerSproutImportImporters()
	{
		return array(
			new SproutSeo_RedirectSproutImportElementImporter()
		);
	}

	/**
	 * Register any supported URL-enabled Section Types
	 *
	 * @return array
	 */
	public function registerSproutSeoUrlEnabledSectionTypes()
	{
		$sections = array(
			new SproutSeo_EntryUrlEnabledSectionType(),
			new SproutSeo_CategoryUrlEnabledSectionType()
		);

		$craftCommercePlugin = craft()->plugins->getPlugin('commerce');

		if (isset($craftCommercePlugin))
		{
			array_push($sections, new SproutSeo_CommerceProductUrlEnabledSectionType());
		}

		return $sections;
	}

	public function registerSproutSeoSchemas()
	{
		return array(
			new SproutSeo_WebsiteIdentityOrganizationSchema(),
			new SproutSeo_WebsiteIdentityPersonSchema(),
			new SproutSeo_WebsiteIdentityWebsiteSchema(),
			new SproutSeo_WebsiteIdentityPlaceSchema(),
			new SproutSeo_ContactPointSchema(),
			new SproutSeo_ImageObjectSchema(),
			new SproutSeo_MainEntityOfPageSchema(),

			new SproutSeo_ThingSchema(),
			new SproutSeo_CreativeWorkSchema(),
			new SproutSeo_EventSchema(),
			new SproutSeo_IntangibleSchema(),
			new SproutSeo_OrganizationSchema(),
			new SproutSeo_PersonSchema(),
			new SproutSeo_PlaceSchema(),
			new SproutSeo_ProductSchema()
		);
	}

	public function onAfterInstall()
	{
		craft()->sproutSeo_redirects->installDefaultSettings();
		craft()->sproutSeo_globalMetadata->installDefaultGlobalMetadata();
	}

	/**
	 * Override SproutImportPlugin::log() method to allow the logging of
	 * multiple messages and arrays
	 *
	 * Examples:
	 *
	 * Standard log:
	 * SproutImportPlugin::log($msg);
	 *
	 * Enhanced log:
	 * $messages['thing1'] = Craft::t('Something happened');
	 * $messages['thing2'] = $model->getErrors();
	 * SproutImportPlugin::log($messages);
	 *
	 * @param string $messages
	 * @param string $level
	 * @param bool   $force
	 *
	 * @return null - writes log to logfile
	 */
	public static function log($messages, $level = LogLevel::Info, $force = false)
	{
		$msg = "";

		if (is_array($messages))
		{
			foreach ($messages as $message)
			{
				$msg .= PHP_EOL . print_r($message, true);
			}
		}
		else
		{
			$msg = $messages;
		}

		parent::log($msg, $level = LogLevel::Info, $force = false);
	}
}

/**
 * @return SproutSeoService
 */
function sproutSeo()
{
	return Craft::app()->getComponent('sproutSeo');
}
