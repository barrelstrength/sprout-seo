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
		$output = sproutSeo()->metadata->getMetaTagHtml();

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
		return sproutSeo()->metadata->getMetaTagGroups();
	}

	/**
	 * Returns all templates
	 *
	 * @param array $urls to avoid
	 *
	 * @return mixed
	 */
	public function getCustomMetaTagGroups($urls)
	{
		return sproutSeo()->metadata->getCustomMetaTagGroups($urls);
	}

	/**
	 * Returns all templates
	 *
	 * @param array $urls to avoid
	 *
	 * @return mixed
	 */
	public function getCustomSiteMaps($urls)
	{
		return sproutSeo()->metadata->getCustomSiteMaps($urls);
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
		return sproutSeo()->metadata->getMetadataGroupById($id);
	}

	/**
	 * @param $handle
	 *
	 * @return SproutSeo_MetadataModel
	 */
	public function getMetaTagGroupByHandle($handle)
	{
		return sproutSeo()->metadata->getMetadataGroupByHandle($handle);
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
	 * Returns metadata group info
	 *
	 * @return array
	 */
	public function getMetadataInfo($info)
	{
		return sproutSeo()->metadata->getMetadataInfo($info);
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
		return sproutSeo()->schema->getKnowledgeGraphLinkedData();
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
		$tree       = file_get_contents(CRAFT_PLUGINS_PATH . 'sproutseo/resources/jsonld/tree.jsonld');
		$json       = json_decode($tree, true);
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
						'icon'  => 'ABCD'
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
						'label'       => "Bing Webmaster Tools",
						'value'       => 'bingWebmasterTools',
						'metaTagName' => 'msvalidate.01'
					),
					array(
						'label'       => "Facebook App ID",
						'value'       => 'facebookAppId',
						'metaTagName' => 'fb:app_id'
					),
					array(
						'label'       => "Facebook Page",
						'value'       => 'FacebookPage',
						'metaTagName' => 'fb:app_id'
					),
					array(
						'label'       => "Google Search Console",
						'value'       => 'googleSearchConsole',
						'metaTagName' => 'google-site-verification'
					),
					array(
						'label' => "Pinterest",
						'value' => 'pinterest', '
							metaTagName' => 'p:domain_verify'
					),
					array(
						'label'       => "Yandex Webmaster Tools",
						'value'       => 'yandexWebmasterTools',
						'metaTagName' => 'yandex-verification'
					)
				);

				break;
		}

		return $options;
	}

	public function getFinalOptions($schemaType, $handle)
	{
		$schemaGlobals = sproutSeo()->schema->getGlobals();
		$options       = $this->getGlobalOptions($schemaType);

		array_push($options, array('optgroup' => 'Custom'));

		$schemas = $schemaGlobals->{$schemaType} != null ? $schemaGlobals->{$schemaType} : array();

		foreach ($schemas as $schema)
		{
			if (!$this->isCustomValue($schemaType, $schema[$handle]))
			{
				array_push($options, array('label' => $schema[$handle], 'value' => $schema[$handle]));
			}
		}

		array_push($options, array('label' => 'Add Custom', 'value' => 'custom'));

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

	public function getGenderOptions()
	{
		$schemaType = "identity";
		$options    = array(
			array(
				'label' => "Select...",
				'value' => ''
			),
			array(
				'label' => "Female",
				'value' => 'female'
			),
			array(
				'label' => "Male",
				'value' => 'male',
			)
		);

		$schemaGlobals = sproutSeo()->schema->getGlobals();
		$gender        = $schemaGlobals[$schemaType]['gender'];

		array_push($options, array('optgroup' => 'Custom'));

		if (!array_key_exists($gender, array('female' => 0, 'male' => 1)) && $gender != '')
		{
			array_push($options, array('label' => $gender, 'value' => $gender));
		}

		array_push($options, array('label' => 'Add Custom', 'value' => 'custom'));

		return $options;
	}

	public function getAppenedMetaTitleOptions()
	{
		$options = array(
			array(
				'label' => "Select...",
				'value' => ''
			),
			array(
				'label' => "Site Name",
				'value' => 'sitename'
			)
		);

		$schemaGlobals = sproutSeo()->schema->getGlobals();

		if (isset($schemaGlobals['settings']['appendTitleValue']))
		{
			$appendTitleValue = $schemaGlobals['settings']['appendTitleValue'];

			array_push($options, array('optgroup' => 'Custom'));

			if (!array_key_exists($appendTitleValue, array('sitename' => 0)) && $appendTitleValue != '')
			{
				array_push($options, array('label' => $appendTitleValue, 'value' => $appendTitleValue));
			}
		}

		array_push($options, array('label' => 'Add Custom', 'value' => 'custom'));

		return $options;
	}

	public function getCharacterSegmentOptions()
	{
		$options = array(
			array(
				'label' => "Select...",
				'value' => ''
			),
			array(
				'label' => "-",
				'value' => '-'
			),
			array(
				'label' => "•",
				'value' => '•'
			),
			array(
				'label' => "|",
				'value' => '|'
			),
			array(
				'label' => "/",
				'value' => '/'
			),
			array(
				'label' => ":",
				'value' => ':'
			),
		);

		$schemaGlobals = sproutSeo()->schema->getGlobals();

		if (isset($schemaGlobals['settings']['seoDivider']))
		{
			$seoDivider    = $schemaGlobals['settings']['seoDivider'];

			array_push($options, array('optgroup' => 'Custom'));

			if (!array_key_exists($seoDivider, array('-'=>0, '•'=>1, '|'=>2, '/'=>3, ':'=>4)) && $seoDivider != '')
			{
				array_push($options, array('label' => $seoDivider, 'value' => $seoDivider));
			}
		}

		array_push($options, array('label' => 'Add Custom', 'value' => 'custom'));

		return $options;
	}

	/**
	 * Returns all plain fields available given a type
	 *
	 * @return array
	 */
	public function getOptimizedOptions($type = "PlainText", $handle = null, $settings = null)
	{
		$options = array();
		$fields  = craft()->fields->getAllFields();

		$options[''] = "Select...";
		$options[]   = array('optgroup' => "Use Existing Field");

		if ($handle == 'optimizedTitleField')
		{
			$options['elementTitle'] = "Title";
		}

		foreach ($fields as $key => $field)
		{
			if ($field->type == $type)
			{
				$context             = explode(":", $field->context);
				$context             = isset($context[0]) ? $context[0] : 'global';
				$options[$field->id] = $field->name;
			}
		}

		$options[]           = array('optgroup' => "Add Custom Field");

		if (!isset($options[$settings[$handle]]) && $settings[$handle] != 'manually')
		{
			$options[$settings[$handle]] = $settings[$handle];
		}

		if ($type != 'Assets')
		{
			$options['custom'] = 'Custom Format';
		}

		$options['manually'] = 'Manual Field';

		return $options;
	}

	/**
	 * Returns all plain fields available given a type
	 *
	 * @return array
	 */
	public function getOptimizedTitleOptions($settings)
	{
		return $this->getOptimizedOptions('PlainText', 'optimizedTitleField', $settings);
	}

	/**
	 * Returns all plain fields available given a type
	 *
	 * @return array
	 */
	public function getOptimizedDescriptionOptions($settings)
	{
		return $this->getOptimizedOptions('PlainText', 'optimizedDescriptionField', $settings);
	}

	/**
	 * Returns all plain fields available given a type
	 *
	 * @return array
	 */
	public function getOptimizedAssetsOptions()
	{
		return $this->getOptimizedOptions("Assets");
	}

	/**
	 * Returns all plain fields available given a type
	 *
	 * @return array
	 */
	public function getGlobalRobots()
	{
		$globals = sproutSeo()->schema->getGlobals();
		$robots  = SproutSeoOptimizeHelper::getRobotsMetaValue($globals->robots);

		return SproutSeoOptimizeHelper::prepRobotsForSettings($robots);
	}

	public function getLocaleOptions()
	{
		$locales = array(
			array(
				'value' => '',
				'label' => 'Select locale...'
			)
		);

		foreach (craft()->i18n->getAllLocales() as $locale)
		{
			array_push($locales, array(
				'value' => $locale->id,
				'label' => $locale->name . " (" . $locale->id . ")"
			));
		}

		return $locales;
	}

	/**
	 * Returns registerSproutSeoSchemaMaps hook
	 *
	 * @return array
	 */
	public function getSchemaMaps()
	{
		$schemaMaps = craft()->plugins->call('registerSproutSeoSchemaMaps');

		return $schemaMaps;
	}

	/**
	 * Returns registerSproutSeoSchemaMaps hook
	 *
	 * @return array
	 */
	public function getSchemaMapsArray()
	{
		$schemaMaps = $this->getSchemaMaps();
		$schemas    = array('' => 'Select...');

		array_push($schemas, array('optgroup' => 'Basic'));

		foreach ($schemaMaps as $schemasByPlugin)
		{
			foreach ($schemasByPlugin as $schema)
			{
				$type           = $schema->getType();
				$schemas[$type] = $schema->getName();
			}
		}

		array_push($schemas, array('optgroup' => 'Custom'));

		return $schemas;
	}

	/**
	 * Returns global contacts
	 *
	 * @return array
	 */
	public function getContacts()
	{
		$contacts = sproutSeo()->schema->getGlobals()->contacts;

		$contacts = $contacts ? $contacts : array();

		foreach ($contacts as &$contact)
		{
			$contact['type'] = $contact['contactType'];
			unset($contact['contactType']);
		}

		return $contacts;
	}

	/**
	 * Returns global social profiles
	 *
	 * @return array
	 */
	public function getSocialProfiles()
	{
		$socials = sproutSeo()->schema->getGlobals()->social;

		$socials = $socials ? $socials : array();

		foreach ($socials as &$social)
		{
			$social['name'] = $social['profileName'];
			unset($social['profileName']);
		}

		return $socials;
	}
}
