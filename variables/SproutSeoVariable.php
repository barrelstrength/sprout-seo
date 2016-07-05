<?php
namespace Craft;

/**
 * Class SproutSeoVariable
 *
 * @package Craft
 */
class SproutSeoVariable
{
	/**
	 * @var SproutSeoPlugin
	 */
	protected $plugin;

	public function __construct()
	{
		$this->plugin = craft()->plugins->getPlugin('sproutseo');
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->plugin->getName();
	}

	/**
	 * @return string
	 */
	public function getVersion()
	{
		return $this->plugin->getVersion();
	}

	/**
	 * Sets SEO metadata in templates
	 *
	 * @param array $meta Array of supported meta values
	 */
	public function meta(array $meta = array())
	{
		if (count($meta))
		{
			sproutSeo()->optimize->updateMeta($meta);
		}
	}

	/**
	 * Processes and outputs SEO meta tags
	 *
	 * @return \Twig_Markup
	 */
	public function optimize()
	{
		$output = sproutSeo()->optimize->optimize();

		return TemplateHelper::getRaw($output);
	}

	/**
	 * Prepare an array of the optimized Meta
	 *
	 * @return multi-dimensional array
	 */
	public function getOptimizedMeta()
	{
		$prioritizedMetaTagModel = sproutSeo()->optimize->getOptimizedMeta();

		return $prioritizedMetaTagModel->getMetaTagData();
	}

	/**
	 * @deprecated Deprecated in favor of optimize()
	 *
	 */
	public function define($overrideInfo)
	{
		craft()->deprecator->log('{{ craft.sproutSeo.define() }}', '<code>{{ craft.sproutSeo.define() }}</code> has been deprecated. Use <code>{{ craft.sproutSeo.optimize() }}</code> instead.');

		return $this->optimize($overrideInfo);
	}

	/**
	 * Returns all templates
	 *
	 * @return mixed
	 */
	public function getMetaTagGroups()
	{
		return sproutSeo()->metaTags->getMetaTagGroups();
	}

	/**
	 * Returns a specific template if found
	 *
	 * @param int $id
	 *
	 * @return null|mixed
	 */
	public function getMetaTagGroupById($id)
	{
		return sproutSeo()->metaTags->getMetaTagGroupById($id);
	}

	/**
	 * @param $handle
	 *
	 * @return SproutSeo_MetaTagsModel
	 */
	public function getMetaTagGroupByHandle($handle)
	{
		return sproutSeo()->metaTags->getMetaTagGroupByHandle($handle);
	}

	/**
	 * @param null $metaTagGroupId
	 *
	 * @return mixed
	 */
	public function globalFallbackId()
	{
		return sproutSeo()->metaTags->globalFallbackId();
	}

	/**
	 * @param array $options
	 *
	 * @return string
	 */
	public function getSitemap(array $options = null)
	{
		return sproutSeo()->sitemap->getSitemap($options);
	}

	/**
	 * Returns all custom pages for sitemap settings
	 *
	 * @return array of Sections
	 */
	public function getAllCustomPages()
	{
		return sproutSeo()->sitemap->getAllCustomPages();
	}

	/**
	 * Returns all sitemaps
	 *
	 * @return array of Sections
	 */
	public function getAllSitemaps()
	{
		return sproutSeo()->sitemap->getAllSitemaps();
	}

	/**
	 * Returns all custom names
	 *
	 * @return array of Sections
	 */
	public function getAllCustomNames()
	{
		return sproutSeo()->sitemap->getAllCustomNames();
	}

	public function getDivider()
	{
		return craft()->plugins->getPlugin('sproutseo')->getSettings()->seoDivider;
	}

	public function getSettings()
	{
		return craft()->plugins->getPlugin('sproutseo')->getSettings();
	}

	public function getGlobals()
	{
		return sproutSeo()->schema->getGlobals();
	}

	public function getKnowledgeGraphLinkedData()
	{
		return sproutSeo()->optimize->getKnowledgeGraphLinkedData();
	}

	public function getAssetElementType()
	{
		$elementType = craft()->elements->getElementType(ElementType::Asset);

		return $elementType;
	}

	public function getElementById($id)
	{
		$element = craft()->elements->getElementById($id);

		return $element != null ? $element : false;
	}


	public function getOrganizationOptions()
	{
		$tree = file_get_contents(CRAFT_PLUGINS_PATH . 'sproutseo/resources/jsonld/tree.jsonld');
		$json = json_decode($tree, true);
		$jsonByName = array();

		foreach ($json['children'] as $key => $value)
		{
			if ($value['name'] === 'Organization')
			{
				$json = $value['children'];
				break;
			}
		}

		foreach ($json as $key => $value)
		{
			$jsonByName[$value['name']] = $value;
		}

		return $jsonByName;
	}

	public function getDate($string)
	{
		$date = new DateTime($string);

		return $date;
	}

	public function getJsonName($description)
	{
		$name = preg_replace('/(?<!^)([A-Z])/', ' \\1', $description);

		if ($description == 'NGO')
		{
			$name = 'Non Government Organization';
		}

		return $name;
	}

	/**
	 * Returns global options given a schema type
	 *
	 * @return array
	 */
	public function getGlobalOptions($schemaType)
	{
		$options = array();

		switch ($schemaType)
		{
			case 'contacts':

				$options = array(
						array(
							'label' => "Select Type...",
							'value' => ''
						),
						array(
							'label' => "Customer Service",
							'value' => 'customer service'
						),
						array(
							'label' => "Technical Support",
							'value' => 'technical support'
						),
						array(
							'label' => "Billing Support",
							'value' => 'billing support'
						),
						array(
							'label' => "Bill Payment",
							'value' => 'bill payment'
						),
						array(
							'label' => "Sales",
							'value' => 'sales'
						),
						array(
							'label' => "Reservations",
							'value' => 'reservations'
						),
						array(
							'label' => "Credit Card Support",
							'value' => 'credit card support'
						),
						array(
							'label' => "Emergency",
							'value' => 'emergency'
						),
						array(
							'label' => "Baggage Tracking",
							'value' => 'baggage tracking'
						),
						array(
							'label' => "Roadside Assistance",
							'value' => 'roadside assistance'
						),
						array(
							'label' => "Package Tracking",
							'value' => 'package tracking'
						)
					);

				break;

			case 'social':

				$options = array(
						array(
							'label' => "Select...",
							'value' => ''
						),
						array(
							'label' => "Facebook",
							'value' => 'Facebook'
						),
						array(
							'label' => "Twitter",
							'value' => 'Twitter'
						),
						array(
							'label' => "Google+",
							'value' => 'Google+'
						),
						array(
							'label' => "Instagram",
							'value' => 'Instagram',
							'icon' => 'ABCD'
						),
						array(
							'label' => "YouTube",
							'value' => 'YouTube'
						),
						array(
							'label' => "LinkedIn",
							'value' => 'LinkedIn'
						),
						array(
							'label' => "Myspace",
							'value' => 'Myspace'
						),
						array(
							'label' => "Pinterest",
							'value' => 'Pinterest'
						),
						array(
							'label' => "SoundCloud",
							'value' => 'SoundCloud'
						),
						array(
							'label' => "Tumblr",
							'value' => 'Tumblr'
						)
					);

				break;

			case 'ownership':

				$options = array(
						array(
							'label' => "Select...",
							'value' => ''
						),
						array(
							'label' => "Bing Webmaster Tools",
							'value' => 'bingWebmasterTools',
							'metaTagName' => 'msvalidate.01'
						),
						array(
							'label' => "Facebook App ID",
							'value' => 'facebookAppId',
							'metaTagName' => 'fb:app_id'
						),
						array(
							'label' => "Facebook Page",
							'value' => 'FacebookPage',
							'metaTagName' => 'fb:app_id'
						),
						array(
							'label' => "Google Search Console",
							'value' => 'googleSearchConsole',
							'metaTagName' => 'google-site-verification'
						),
						array(
							'label' => "Pinterest",
							'value' => 'pinterest', '
							metaTagName' => 'p:domain_verify'
						),
						array(
							'label' => "Yandex Webmaster Tools",
							'value' => 'yandexWebmasterTools',
							'metaTagName' => 'yandex-verification'
						)
					);

				break;
		}

		return $options;
	}

	public function getFinalOptions($schemaType, $handle)
	{
		$options = array();

		$schemaGlobals = sproutSeo()->schema->getGlobals();
		$isCustom      = false;
		$options       = $this->getGlobalOptions($schemaType);

		foreach ($schemaGlobals[$schemaType] as $shema)
		{
			if (!$this->isCustomValue($schemaType, $shema[$handle]))
			{
				if (!$isCustom)
				{
					array_push($options, array('label'=>'---', 'value'=>''));
				}

				$isCustom = true;
				array_push($options, array('label'=>$shema[$handle], 'value'=>$shema[$handle]));
			}
		}

		array_push($options, array('label'=>'---', 'value'=>''));
		array_push($options, array('label'=>'Custom', 'value'=>'custom'));

		return $options;
	}

	/**
	 * Verifies on the Global Options array if option value given is custom
	 *
	 * @return boolean
	 */
	public function isCustomValue($schemaType, $value)
	{
		$options = $this->getGlobalOptions($schemaType);

		foreach ($options as $option)
		{
			if ($option['value'] == $value)
			{
				return true;
			}
		}

		return false;
	}

}
