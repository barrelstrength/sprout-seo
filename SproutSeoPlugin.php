<?php
/**
 * @author    Barrel Strength Design LLC <sprout@barrelstrengthdesign.com>
 * @copyright Copyright (c) 2016, Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 * @see       http://sprout.barrelstrengthdesign.com
 */

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
		return 'Content-focused SEO. Control every detail of your online visibility.';
	}

	/**
	 * @return string
	 */
	public function getVersion()
	{
		return '3.3.4';
	}

	/**
	 * @return string
	 */
	public function getSchemaVersion()
	{
		return '3.3.4';
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
		// Import third party libraries
		require_once dirname(__FILE__) . '/vendor/autoload.php';
		$baseIntegrations = 'plugins.sproutseo.integrations.sproutseo.';

		Craft::import('plugins.sproutseo.helpers.SproutSeoOptimizeHelper');

		Craft::import('plugins.sproutseo.contracts.SproutSeoBaseUrlEnabledSectionType');
		Craft::import($baseIntegrations . 'sectiontypes.SproutSeo_EntryUrlEnabledSectionType');
		Craft::import($baseIntegrations . 'sectiontypes.SproutSeo_CategoryUrlEnabledSectionType');
		Craft::import($baseIntegrations . 'sectiontypes.SproutSeo_CommerceProductUrlEnabledSectionType');

		Craft::import('plugins.sproutseo.contracts.SproutSeoBaseSchema');
		Craft::import($baseIntegrations . 'schema.SproutSeo_WebsiteIdentityOrganizationSchema');
		Craft::import($baseIntegrations . 'schema.SproutSeo_WebsiteIdentityPersonSchema');
		Craft::import($baseIntegrations . 'schema.SproutSeo_WebsiteIdentityWebsiteSchema');
		Craft::import($baseIntegrations . 'schema.SproutSeo_WebsiteIdentityPlaceSchema');
		Craft::import($baseIntegrations . 'schema.SproutSeo_ContactPointSchema');
		Craft::import($baseIntegrations . 'schema.SproutSeo_ImageObjectSchema');
		Craft::import($baseIntegrations . 'schema.SproutSeo_MainEntityOfPageSchema');
		Craft::import($baseIntegrations . 'schema.SproutSeo_PostalAddressSchema');
		Craft::import($baseIntegrations . 'schema.SproutSeo_GeoSchema');

		Craft::import($baseIntegrations . 'schema.SproutSeo_ThingSchema');
		Craft::import($baseIntegrations . 'schema.SproutSeo_CreativeWorkSchema');
		Craft::import($baseIntegrations . 'schema.SproutSeo_EventSchema');
		Craft::import($baseIntegrations . 'schema.SproutSeo_IntangibleSchema');
		Craft::import($baseIntegrations . 'schema.SproutSeo_OrganizationSchema');
		Craft::import($baseIntegrations . 'schema.SproutSeo_PersonSchema');
		Craft::import($baseIntegrations . 'schema.SproutSeo_PlaceSchema');
		Craft::import($baseIntegrations . 'schema.SproutSeo_ProductSchema');

		Craft::import('plugins.sproutseo.integrations.sproutimport.SproutSeo_RedirectSproutImportElementImporter');

		Craft::import('plugins.sproutseo.helpers.SproutSeoAddressHelper');

		if (!craft()->isConsole())
		{
			// @todo - this should also be possible from the console however
			//         we need to wait until the URL-enabled element types don't rely on URL
			//         in the resaveElements method
			craft()->on('fields.onSaveFieldLayout', function (Event $event)
			{
				sproutSeo()->elementMetadata->resaveElements($event);
			});

			craft()->onException = function (\CExceptionEvent $event)
			{
				if ((($event->exception instanceof \CHttpException) && ($event->exception->statusCode == 404)) || (($event->exception->getPrevious() instanceof \CHttpException) && ($event->exception->getPrevious()->statusCode == 404)))
				{
					if (craft()->request->isSiteRequest() && !craft()->request->isLivePreview())
					{
						$url = craft()->request->getUrl();

						// check if the request url needs redirect
						$redirect = sproutSeo()->redirects->getRedirect($url);

						$plugin      = craft()->plugins->getPlugin('sproutseo');
						$seoSettings = $plugin->getSettings();

						if (!$redirect && $seoSettings->enable404RedirectLog)
						{
							// Save new 404 Redirect
							$redirect = sproutSeo()->redirects->save404Redirect($url);
						}

						if ($redirect)
						{
							sproutSeo()->redirects->logRedirect($redirect->id);

							// Use != instead of !== as 404 can be both as integer or string
							if ($redirect->enabled && $redirect->method != 404)
							{
								// Redirect away
								craft()->request->redirect($redirect->newUrl, true, $redirect->method);
							}
						}
					}
				}
			};

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
			'structureId'             => array(AttributeType::Number, 'default' => null),
			'twitterCard'             => array(AttributeType::String, 'default' => null),
			'ogType'                  => array(AttributeType::String, 'default' => null),
			'localeIdOverride'        => array(AttributeType::String, 'default' => null),
			'displayFieldHandles'     => array(AttributeType::Bool, 'default' => false),
			'enableCustomSections'    => array(AttributeType::Bool, 'default' => false),
			'enableMetaDetailsFields' => array(AttributeType::Bool, 'default' => false),
			'enableMetadataRendering' => array(AttributeType::Bool, 'default' => true),
			'metadataVariable'        => array(AttributeType::String, 'default' => null),
			'twitterTransform'        => array(AttributeType::String, 'default' => null),
			'ogTransform'             => array(AttributeType::String, 'default' => null),
			'totalElementsPerSitemap' => array(AttributeType::Number, 'default' => 500),
			'enableDynamicSitemaps'   => array(AttributeType::Bool, 'default' => true),
			'enable404RedirectLog'    => array(AttributeType::Bool, 'default' => false),
			'total404Redirects'       => array(AttributeType::Number, 'default' => 1000)
		);
	}

	/**
	 * @return array
	 */
	public function registerCpRoutes()
	{
		return array(
			'sproutseo/sections/new'                        => array(
				'action' => 'sproutSeo/sectionMetadata/sectionMetadataEditTemplate'
			),
			'sproutseo/sections/(?P<sectionMetadataId>\d+)' => array(
				'action' => 'sproutSeo/sectionMetadata/sectionMetadataEditTemplate'
			),
			'sproutseo/redirects'                           => array(
				'action' => 'sproutSeo/redirects/redirectIndex'
			),
			'sproutseo/redirects/new'                       => array(
				'action' => 'sproutSeo/redirects/editRedirect'
			),
			'sproutseo/redirects/(?P<redirectId>\d+)'       => array(
				'action' => 'sproutSeo/redirects/editRedirect'
			),
			'sproutseo/settings'                            => array(
				'action' => 'sproutSeo/settings/settingsIndex'
			),
			'sproutseo/settings/(?P<settingsTemplate>.*)'   => array(
				'action' => 'sproutSeo/settings/settingsIndex'
			),
		);
	}

	/**
	 * Match dynamic sitemap URLs
	 *
	 * Example matches include:
	 * - sitemap.xml
	 * - singles-sitemap.xml
	 * - custom-sections-sitemap.xml
	 * - blog-entries-sitemap1.xml
	 * - blog-entries-sitemap2.xml
	 *
	 * @return array
	 */
	public function registerSiteRoutes()
	{
		$plugin      = craft()->plugins->getPlugin('sproutseo');
		$seoSettings = $plugin->getSettings();

		if (isset($seoSettings->enableDynamicSitemaps) && $seoSettings->enableDynamicSitemaps)
		{
			return array(
				'(.+-)?sitemap(\d+)?.xml'  => array(
					'action' => 'sproutSeo/sitemap/index'
				)
			);
		}
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
			new SproutSeo_PostalAddressSchema(),
			new SproutSeo_GeoSchema(),

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
