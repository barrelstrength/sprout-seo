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

	/**
	 * SproutSeoVariable constructor.
	 */
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
		$output = sproutSeo()->optimize->getMetaTagHtml();

		return TemplateHelper::getRaw($output);
	}

	/**
	 * Prepare an array of the optimized Meta
	 *
	 * @return multi-dimensional array
	 */
	public function getOptimizedMeta()
	{
		$prioritizedMetadataModel = sproutSeo()->optimize->getOptimizedMeta();

		return $prioritizedMetadataModel->getMetaTagData();
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
	public function getSectionMetadata()
	{
		return sproutSeo()->sectionMetadata->getSectionMetadata();
	}

	/**
	 * Returns a specific template if found
	 *
	 * @param int $id
	 *
	 * @return null|mixed
	 */
	public function getSectionMetadataById($id)
	{
		return sproutSeo()->sectionMetadata->getSectionMetadataById($id);
	}

	/**
	 * @param $handle
	 *
	 * @return SproutSeo_MetadataModel
	 */
	public function getSectionMetadataByHandle($handle)
	{
		return sproutSeo()->sectionMetadata->getSectionMetadataByHandle($handle);
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
	 * Returns all URL-Enabled Sections Types
	 *
	 * @return array of Sections
	 */
	public function getUrlEnabledSectionTypes()
	{
		return sproutSeo()->sectionMetadata->getUrlEnabledSectionTypes();
	}

	/**
	 * Returns all custom pages for sitemap settings
	 *
	 * @return array of Sections
	 */
	public function getCustomSections()
	{
		return sproutSeo()->sectionMetadata->getCustomSections();
	}

	/**
	 * @return mixed
	 */
	public function getDivider()
	{
		return craft()->plugins->getPlugin('sproutseo')->getSettings()->seoDivider;
	}

	/**
	 * @return BaseModel
	 */
	public function getSettings()
	{
		return craft()->plugins->getPlugin('sproutseo')->getSettings();
	}

	/**
	 * @return BaseModel
	 */
	public function getGlobalMetadata()
	{
		return sproutSeo()->globalMetadata->getGlobalMetadata();
	}

	/**
	 * @return \Twig_Markup
	 */
	public function getKnowledgeGraphLinkedData()
	{
		return sproutSeo()->schema->getKnowledgeGraphLinkedData();
	}

	/**
	 * @return IElementType|null
	 */
	public function getAssetElementType()
	{
		$elementType = craft()->elements->getElementType(ElementType::Asset);

		return $elementType;
	}

	/**
	 * @param $id
	 *
	 * @return bool|BaseElementModel|null
	 */
	public function getElementById($id)
	{
		$element = craft()->elements->getElementById($id);

		return $element != null ? $element : false;
	}

	/**
	 * @return array
	 */
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

	/**
	 * @param $string
	 *
	 * @return DateTime
	 */
	public function getDate($string)
	{
		$date = new DateTime($string);

		return $date;
	}

	/**
	 * @param $description
	 *
	 * @return mixed|string
	 */
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
						'value'       => 'facebookPage',
						'metaTagName' => 'fb:page_id'
					),
					array(
						'label'       => "Facebook Admins",
						'value'       => 'facebookAdmins',
						'metaTagName' => 'fb:admins'
					),
					array(
						'label'       => "Google Search Console",
						'value'       => 'googleSearchConsole',
						'metaTagName' => 'google-site-verification'
					),
					array(
						'label'       => "Pinterest",
						'value'       => 'pinterest',
						'metaTagName' => 'p:domain_verify'
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

	/**
	 * @param $schemaType
	 * @param $handle
	 *
	 * @return array
	 */
	public function getFinalOptions($schemaType, $handle)
	{
		$schemaGlobals = sproutSeo()->globalMetadata->getGlobalMetadata();
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

	/**
	 * @return array
	 */
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

		$schemaGlobals = sproutSeo()->globalMetadata->getGlobalMetadata();
		$gender        = $schemaGlobals[$schemaType]['gender'];

		array_push($options, array('optgroup' => 'Custom'));

		if (!array_key_exists($gender, array('female' => 0, 'male' => 1)) && $gender != '')
		{
			array_push($options, array('label' => $gender, 'value' => $gender));
		}

		array_push($options, array('label' => 'Add Custom', 'value' => 'custom'));

		return $options;
	}

	/**
	 * @return array
	 */
	public function getAppendMetaTitleOptions()
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

		$schemaGlobals = sproutSeo()->globalMetadata->getGlobalMetadata();

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

	/**
	 * @return array
	 */
	public function getSeoDividerOptions()
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

		$schemaGlobals = sproutSeo()->globalMetadata->getGlobalMetadata();

		if (isset($schemaGlobals['settings']['seoDivider']))
		{
			$seoDivider = $schemaGlobals['settings']['seoDivider'];

			array_push($options, array('optgroup' => 'Custom'));

			if (!array_key_exists($seoDivider, array('-' => 0, '•' => 1, '|' => 2, '/' => 3, ':' => 4)) && $seoDivider != '')
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

		$options[] = array('optgroup' => "Add Custom Field");

		$options['manually'] = 'Display Editable Field';

		$options[] = array('optgroup' => "Define Custom Pattern");

		if (!isset($options[$settings[$handle]]) && $settings[$handle] != 'manually')
		{
			$options[$settings[$handle]] = $settings[$handle];
		}

		if ($type != 'Assets')
		{
			$options['custom'] = 'Add Custom Format';
		}

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
		$globals = sproutSeo()->globalMetadata->getGlobalMetadata();
		$robots  = SproutSeoOptimizeHelper::prepareRobotsMetadataValue($globals->robots);

		return SproutSeoOptimizeHelper::prepareRobotsMetadataForSettings($robots);
	}

	/**
	 * @return array
	 */
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
	 * Returns registerSproutSeoSchemas hook
	 *
	 * @return array
	 */
	public function getSchemas()
	{
		return sproutSeo()->optimize->getSchemas();
	}

	/**
	 * Returns registerSproutSeoSchemas hook
	 *
	 * @return array
	 */
	public function getSchemaOptions()
	{
		$schemas = sproutSeo()->optimize->getSchemas();

		ksort($schemas);

		foreach ($schemas as $schema)
		{
			if ($schema->isUnlistedSchemaType())
			{
				unset($schemas[$schema->getUniqueKey()]);
			}
		}

		// Get a filtered list of our default Sprout SEO schema
		$defaultSchema = array_filter($schemas, function ($map)
		{
			/**
			 * @var SproutSeoBaseSchema $map
			 */
			return stripos($map->getUniqueKey(), 'craft-sproutseo') !== false;
		});

		// Get a filtered list of of any custom schema
		$customSchema = array_filter($schemas, function ($map)
		{
			/**
			 * @var SproutSeoBaseSchema $map
			 */
			return stripos($map->getUniqueKey(), 'craft-sproutseo') === false;
		});

		// Build our options
		$schemaOptions = array('' => 'Select...', array('optgroup' => 'Default Types'));

		$schemaOptions = array_merge($schemaOptions, array_map(function ($schema)
		{
			return array(
				'label' => $schema->getType(),
				'value' => $schema->getUniqueKey()
			);
		}, $defaultSchema));

		if (count($customSchema))
		{
			array_push($schemaOptions, array('optgroup' => 'Custom Types'));

			$schemaOptions = array_merge($schemaOptions, array_map(function ($schema)
			{
				return array(
					'label' => $schema->getType(),
					'value' => $schema->getUniqueKey()
				);
			}, $customSchema));
		}

		return $schemaOptions;
	}

	/**
	 * Returns global contacts
	 *
	 * @return array
	 */
	public function getContacts()
	{
		$contacts = sproutSeo()->globalMetadata->getGlobalMetadata()->contacts;

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
		$socials = sproutSeo()->globalMetadata->getGlobalMetadata()->social;

		$socials = $socials ? $socials : array();

		foreach ($socials as &$social)
		{
			$social['name'] = $social['profileName'];
			unset($social['profileName']);
		}

		return $socials;
	}

	private function getSchemaChildren($type)
	{
		$tree     = sproutSeo()->schema->getVocabularies($type);
		$children = array();

		// let's assume 3 levels
		if (isset($tree['children']))
		{
			foreach ($tree['children'] as $key => $level1)
			{
				$children[$key] = array();

				if (isset($level1['children']))
				{
					foreach ($level1['children'] as $key2 => $level2)
					{
						$children[$key][$key2] = array();

						if (isset($level2['children']))
						{
							foreach ($level2['children'] as $key3 => $level3)
							{
								array_push($children[$key][$key2], $key3);
							}
						}
					}
				}
			}
		}

		return $children;
	}

	/**
	 * Prepare an array of the optimized Meta
	 *
	 * @return multi-dimensional array
	 */
	public function getSchemaSubtypes($schemas)
	{
		$values = null;

		foreach ($schemas as $schema)
		{
			if (isset($schema['label']))
			{
				$type = $schema['label'];

				$values[$schema['value']] = $this->getSchemaChildren($type);
			}
		}

		return $values;
	}

	/**
	 * Prepare an array of the image transforms available
	 *
	 * @return multi-dimensional array
	 */
	public function getTransforms()
	{
		return sproutSeo()->sectionMetadata->getTransforms();
	}
}
