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
		return 'All-in-One SEO, Social Media Sharing, Redirects, and Sitemap';
	}

	/**
	 * @return string
	 */
	public function getVersion()
	{
		return '2.2.1';
	}

	/**
	 * @return string
	 */
	public function getSchemaVersion()
	{
		return '2.2.2';
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
		Craft::import('plugins.sproutseo.contracts.BaseSproutSeoSchemaMap');
		Craft::import('plugins.sproutseo.helpers.SproutSeoOptimizeHelper');

		Craft::import('plugins.sproutseo.integrations.sproutseo.SproutSeo_ContactPointSchemaMap');
		Craft::import('plugins.sproutseo.integrations.sproutseo.SproutSeo_ImageObjectSchemaMap');
		Craft::import('plugins.sproutseo.integrations.sproutseo.SproutSeo_OrganizationSchemaMap');
		Craft::import('plugins.sproutseo.integrations.sproutseo.SproutSeo_PersonSchemaMap');
		Craft::import('plugins.sproutseo.integrations.sproutseo.SproutSeo_NewsArticleSchemaMap');
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
				craft()->templates->includeJsResource('sproutseo/js/SproutBase.js');
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
			'pluginNameOverride'    => AttributeType::String,
			'seoDivider'            => array(AttributeType::String, 'default' => '-'),
			'structureId'           => array(AttributeType::Number, 'default' => null),
			'twitterCard'           => array(AttributeType::String, 'default' => null),
			'ogType'                => array(AttributeType::String, 'default' => null),
			'localeIdOverride'      => array(AttributeType::String, 'default' => null),
			'enableCustomSections'  => array(AttributeType::Bool, 'default' => false),
			'enableCodeOverrides'   => array(AttributeType::Bool, 'default' => false),
			'advancedCustomization' => array(AttributeType::Bool, 'default' => false),
			'templateFolder'        => array(AttributeType::String, 'default' => null),
		);
	}

	/**
	 * @return array
	 */
	public function registerCpRoutes()
	{
		return array(
			'sproutseo/metadata/new'                      => array(
				'action' => 'sproutSeo/metadata/metadataGroupEditTemplate'
			),
			'sproutseo/metadata/(?P<metadataGroupId>\d+)' => array(
				'action' => 'sproutSeo/metadata/metadataGroupEditTemplate'
			),

			'sproutseo/schema/new' =>
				'sproutseo/schema/_edit',

			'sproutseo/schema/(.*)' => array(
				'action' => 'sproutSeo/schema/schemaEditTemplate'
			),

			'sproutseo/sitemap'                               => array(
				'action' => 'sproutSeo/sitemap/sitemapIndex'
			),
			'sproutseo/sitemap/newPage'                       => array(
				'action' => 'sproutSeo/sitemap/editSitemap'
			),
			'sproutseo/settings'                              => array(
				'action' => 'sproutSeo/settings/settingsIndex'
			),
			'sproutseo/settings/(?P<settingsTemplate>.*)/new' => 'sproutseo/settings/schema/_edit',
			'sproutseo/settings/(?P<settingsTemplate>.*)'     => array(
				'action' => 'sproutSeo/settings/settingsIndex'
			),
			'sproutseo/redirects'                             => array(
				'action' => 'sproutSeo/redirects/redirectIndex'
			),
			'sproutseo/redirects/new'                         => array(
				'action' => 'sproutSeo/redirects/editRedirect'
			),
			'sproutseo/redirects/(?P<redirectId>\d+)'         => array(
				'action' => 'sproutSeo/redirects/editRedirect'
			)
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
	 * Returns supported sitemap urls by default.
	 *
	 * 'name_of_the_craft_element_table' => array(
	 *    'name' => Name that will display in the sitemaps and metada UI
	 *    'elementType' => Element Type class name
	 *    'elementGroupId' => column name for the element id
	 *    'service' => service class name
	 *    'method'  => method name to get all elements
	 *    'matchedElementVariable'  => Variable name to be called from the templates
	 *
	 * @return array
	 */
	public function registerSproutSeoSitemap()
	{
		return array(
			'sections'         => array(
				'name'                   => 'Sections',
				'elementType'            => ElementType::Entry,
				'elementGroupId'         => 'sectionId',
				'service'                => 'sections',
				'method'                 => 'getAllSections',
				'matchedElementVariable' => 'entry'
			),
			'categories'       => array(
				'name'                   => 'Categories',
				'elementType'            => ElementType::Category,
				'elementGroupId'         => 'groupId',
				'service'                => 'categories',
				'method'                 => 'getAllGroups',
				'matchedElementVariable' => 'category'
			),
			'commerce_products' => array(
				'name'                   => "Commerce Products",
				'elementType'            => 'Commerce_Product',
				'elementGroupId'         => 'typeId',
				'service'                => 'commerce_productTypes',
				'method'                 => 'getAllProductTypes',
				'matchedElementVariable' => 'product'
			)
		);
	}

	public function registerSproutSeoSchemaMaps()
	{
		return array(
			new SproutSeo_ContactPointSchemaMap(),
			new SproutSeo_ImageObjectSchemaMap(),
			new SproutSeo_OrganizationSchemaMap(),
			new SproutSeo_PersonSchemaMap(),
			new SproutSeo_NewsArticleSchemaMap()
		);
	}

	public function onAfterInstall()
	{
		sproutSeo()->redirects->installDefaultSettings();
		sproutSeo()->schema->installDefaultGlobals();
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
	 * $messages['thing2'] = $entry->getErrors();
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
