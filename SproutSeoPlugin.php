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
		return '2.1.1';
	}

	/**
	 * @return string
	 */
	public function getSchemaVersion()
	{
		return '2.2.1';
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
		return 'sproutseo/settings';
	}

	/* --------------------------------------------------------------
	 * HOOKS
	 * ------------------------------------------------------------ */

	public function init()
	{
		Craft::import('plugins.sproutseo.helpers.SproutSeoMetaHelper');

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
			// @todo Craft 3 - update to use info from config.json
			craft()->templates->includeJsResource('sproutseo/js/brand.js');
			craft()->templates->includeJs("
				sproutFormsBrand = new Craft.SproutBrand();
				sproutFormsBrand.displayFooter({
					pluginName: 'Sprout SEO',
					pluginUrl: 'http://sprout.barrelstrengthdesign.com/craft-plugins/seo',
					pluginVersion: '" . $this->getVersion() . "',
					pluginDescription: '" . $this->getDescription() . "',
					developerName: '(Barrel Strength)',
					developerUrl: '" . $this->getDeveloperUrl() . "'
				});
			");
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
			'pluginNameOverride' => AttributeType::String,
			'seoDivider'         => array(AttributeType::String, 'default' => '-'),
			'structureId'        => array(AttributeType::Number, 'default' => null)
		);
	}

	/**
	 * @return array
	 */
	public function registerCpRoutes()
	{
		return array(
			'sproutseo/defaults/new'                      => array(
				'action' => 'sproutSeo/defaults/editDefault'
			),
			'sproutseo/defaults/(?P<defaultId>\d+)'       => array(
				'action' => 'sproutSeo/defaults/editDefault'
			),
			'sproutseo/sitemap'                           => array(
				'action' => 'sproutSeo/sitemap/sitemapIndex'
			),
			'sproutseo/sitemap/newPage'                   => array(
				'action' => 'sproutSeo/sitemap/editSitemap'
			),
			'sproutseo/settings'                          => array(
				'action' => 'sproutSeo/settings/settingsIndex'
			),
			'sproutseo/settings/(?P<settingsTemplate>.*)' => array(
				'action' => 'sproutSeo/settings/settingsIndex'
			),
			'sproutseo/redirects'                         => array(
				'action' => 'sproutSeo/redirects/redirectIndex'
			),
			'sproutseo/redirects/new'                     => array(
				'action' => 'sproutSeo/redirects/editRedirect'
			),
			'sproutseo/redirects/(?P<redirectId>\d+)'     => array(
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
	 * @return array
	 */
	public function sproutMigrateRegisterElements()
	{
		return array(
			'sproutseo_redirect' => array(
				'model'   => 'Craft\\SproutSeo_Redirect',
				'method'  => 'saveRedirect',
				'service' => 'sproutSeo_redirects',
			)
		);
	}

	public function registerSproutSeoSitemap()
	{
		return array(
			'sections'         => array(
				'name'           => 'Sections',
				'elementType'    => ElementType::Entry,
				'elementGroupId' => 'sectionId',
				'service'        => 'sections',
				'method'         => 'getAllSections',
			),
			'categories'       => array(
				'name'           => 'Categories',
				'elementType'    => ElementType::Category,
				'elementGroupId' => 'groupId',
				'service'        => 'categories',
				'method'         => 'getAllGroups',
			),
			'commerce_product' => array(
				'name'           => 'Commerce Product',
				'elementType'    => 'Commerce_Product',
				'elementGroupId' => 'productTypeId',
				'service'        => 'commerce_productTypes',
				'method'         => 'getAllProductTypes',
			)
		);
	}

	public function onAfterInstall()
	{
		sproutSeo()->redirects->installDefaultSettings();
	}
}

/**
 * @return SproutSeoService
 */
function sproutSeo()
{
	return Craft::app()->getComponent('sproutSeo');
}
