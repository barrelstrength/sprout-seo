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
	 * @deprecated Remove in SproutSeo 4.x in Craft 3.0
	 * @return \Twig_Markup
	 */
	public function optimize()
	{
		$output = "The craft.sproutseo.optimize() tag has been deprecated. Use {% sproutseo 'optimize' %} instead. See a list of all changes in Sprout SEO 3 here: https://sprout.barrelstrengthdesign.com/craft-plugins/seo/docs/getting-started/updating-to-sprout-seo-3";

		throw new Exception(Craft::t($output));
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
		$globals = sproutSeo()->globalMetadata->getGlobalMetadata();
		$divider = '';

		if (isset($globals['settings']['seoDivider']))
		{
			$divider = $globals->settings['seoDivider'];
		}

		return $divider;
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
	 * Returns all the global metadata into one variable
	 *
	 * @return mixed
	 */
	public function getGlobals()
	{
		$globalMetadata = sproutSeo()->globalMetadata->getGlobalMetadata();
		$globalMetadata = $globalMetadata->getAttributes();
		unset($globalMetadata['meta']);
		$identity = $globalMetadata['identity'];
		$organization = null;

		if (isset($identity['organizationSubTypes']))
		{
			foreach ($identity['organizationSubTypes'] as $key => $organizationSubType)
			{
				if ($organizationSubType)
				{
					$organization = $organizationSubType;
				}
			}
		}

		$identity['organization'] = $organization;
		unset($identity['@type']);
		unset($identity['organizationSubTypes']);

		$identity['foundingDate'] = isset($identity['foundingDate']['date']) ? $this->getDate($identity['foundingDate']) : null;

		// we could just get the attributes instead of the model
		$identity['address'] = isset($identity['addressId']) ? sproutSeo()->address->getAddressById($identity['addressId']) : null;

		unset($identity['addressId']);

		$identity['image'] = isset($identity['image'][0]) ? SproutSeoOptimizeHelper::getAssetUrl($identity['image'][0]) : null;

		$globalMetadata['identity'] = $identity;

		$globalMetadata['social']   = $this->getSocialProfiles();

		$globalMetadata['contacts'] = $this->getContacts();

		return $globalMetadata;
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

		return $element != null ? $element : null;
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
		$date = new DateTime($string['date'], new \DateTimeZone(craft()->timezone));

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
			$name = Craft::t('Non Government Organization');
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
						'label' => Craft::t('Select Type...'),
						'value' => ''
					),
					array(
						'label' => Craft::t('Customer Service'),
						'value' => 'customer service'
					),
					array(
						'label' => Craft::t('Technical Support'),
						'value' => 'technical support'
					),
					array(
						'label' => Craft::t('Billing Support'),
						'value' => 'billing support'
					),
					array(
						'label' => Craft::t('Bill Payment'),
						'value' => 'bill payment'
					),
					array(
						'label' => Craft::t('Sales'),
						'value' => 'sales'
					),
					array(
						'label' => Craft::t('Reservations'),
						'value' => 'reservations'
					),
					array(
						'label' => Craft::t('Credit Card Support'),
						'value' => 'credit card support'
					),
					array(
						'label' => Craft::t('Emergency'),
						'value' => 'emergency'
					),
					array(
						'label' => Craft::t('Baggage Tracking'),
						'value' => 'baggage tracking'
					),
					array(
						'label' => Craft::t('Roadside Assistance'),
						'value' => 'roadside assistance'
					),
					array(
						'label' => Craft::t('Package Tracking'),
						'value' => 'package tracking'
					)
				);

				break;

			case 'social':

				$options = array(
					array(
						'label' => Craft::t('Select...'),
						'value' => ''
					),
					array(
						'label' => Craft::t('Facebook'),
						'value' => 'Facebook'
					),
					array(
						'label' => Craft::t('Twitter'),
						'value' => 'Twitter'
					),
					array(
						'label' => Craft::t('Google+'),
						'value' => 'Google+'
					),
					array(
						'label' => Craft::t('Instagram'),
						'value' => 'Instagram',
						'icon'  => 'ABCD'
					),
					array(
						'label' => Craft::t('YouTube'),
						'value' => 'YouTube'
					),
					array(
						'label' => Craft::t('LinkedIn'),
						'value' => 'LinkedIn'
					),
					array(
						'label' => Craft::t('Myspace'),
						'value' => 'Myspace'
					),
					array(
						'label' => Craft::t('Pinterest'),
						'value' => 'Pinterest'
					),
					array(
						'label' => Craft::t('SoundCloud'),
						'value' => 'SoundCloud'
					),
					array(
						'label' => Craft::t('Tumblr'),
						'value' => 'Tumblr'
					)
				);

				break;

			case 'ownership':

				$options = array(
					array(
						'label' => Craft::t('Select...'),
						'value' => ''
					),
					array(
						'label'       => Craft::t('Bing Webmaster Tools'),
						'value'       => 'bingWebmasterTools',
						'metaTagName' => 'msvalidate.01'
					),
					array(
						'label'       => Craft::t('Facebook App ID'),
						'value'       => 'facebookAppId',
						'metaTagName' => 'fb:app_id'
					),
					array(
						'label'       => Craft::t('Facebook Page'),
						'value'       => 'facebookPage',
						'metaTagName' => 'fb:page_id'
					),
					array(
						'label'       => Craft::t('Facebook Admins'),
						'value'       => 'facebookAdmins',
						'metaTagName' => 'fb:admins'
					),
					array(
						'label'       => Craft::t('Google Search Console'),
						'value'       => 'googleSearchConsole',
						'metaTagName' => 'google-site-verification'
					),
					array(
						'label'       => Craft::t('Pinterest'),
						'value'       => 'pinterest',
						'metaTagName' => 'p:domain_verify'
					),
					array(
						'label'       => Craft::t('Yandex Webmaster Tools'),
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

		array_push($options, array('optgroup' => Craft::t('Custom')));

		$schemas = $schemaGlobals->{$schemaType} != null ? $schemaGlobals->{$schemaType} : array();

		foreach ($schemas as $schema)
		{
			if (!$this->isCustomValue($schemaType, $schema[$handle]))
			{
				array_push($options, array('label' => $schema[$handle], 'value' => $schema[$handle]));
			}
		}

		array_push($options, array('label' => Craft::t('Add Custom'), 'value' => 'custom'));

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
	public function getPriceRangeOptions()
	{
		$schemaType = "identity";
		$options    = array(
			array(
				'label' => Craft::t('None'),
				'value' => ''
			),
			array(
				'label' => Craft::t('$'),
				'value' => '$'
			),
			array(
				'label' => Craft::t('$$'),
				'value' => '$$'
			),
			array(
				'label' => Craft::t('$$$'),
				'value' => '$$$'
			),
			array(
				'label' => Craft::t('$$$$'),
				'value' => '$$$$'
			)
		);

		$schemaGlobals = sproutSeo()->globalMetadata->getGlobalMetadata();

		$priceRange = null;

		if (isset($schemaGlobals[$schemaType]['priceRange']))
		{
			$priceRange = $schemaGlobals[$schemaType]['priceRange'];
		}

		array_push($options, array('optgroup' => Craft::t('Custom')));

		if (!array_key_exists($priceRange, array('$' => 0, '$$' => 1, '$$$' => 2, '$$$$' => 4)) && $priceRange != '')
		{
			array_push($options, array('label' => $priceRange, 'value' => $priceRange));
		}

		array_push($options, array('label' => Craft::t('Add Custom'), 'value' => 'custom'));

		return $options;
	}

	/**
	 * @return array
	 */
	public function getGenderOptions()
	{
		$schemaType = "identity";
		$options    = array(
			array(
				'label' => Craft::t('None'),
				'value' => ''
			),
			array(
				'label' => Craft::t('Female'),
				'value' => 'female'
			),
			array(
				'label' => Craft::t('Male'),
				'value' => 'male',
			)
		);

		$schemaGlobals = sproutSeo()->globalMetadata->getGlobalMetadata();
		$gender        = $schemaGlobals[$schemaType]['gender'];

		array_push($options, array('optgroup' => Craft::t('Custom')));

		if (!array_key_exists($gender, array('female' => 0, 'male' => 1)) && $gender != '')
		{
			array_push($options, array('label' => $gender, 'value' => $gender));
		}

		array_push($options, array('label' => Craft::t('Add Custom'), 'value' => 'custom'));

		return $options;
	}

	/**
	 * @return array
	 */
	public function getAppendMetaTitleOptions()
	{
		$options = array(
			array(
				'label' => Craft::t('None'),
				'value' => ''
			),
			array(
				'label' => Craft::t('Site Name'),
				'value' => 'sitename'
			)
		);

		$schemaGlobals = sproutSeo()->globalMetadata->getGlobalMetadata();

		if (isset($schemaGlobals['settings']['appendTitleValue']))
		{
			$appendTitleValue = $schemaGlobals['settings']['appendTitleValue'];

			array_push($options, array('optgroup' => Craft::t('Custom')));

			if (!array_key_exists($appendTitleValue, array('sitename' => 0)) && $appendTitleValue != '')
			{
				array_push($options, array('label' => $appendTitleValue, 'value' => $appendTitleValue));
			}
		}

		array_push($options, array('label' => Craft::t('Add Custom'), 'value' => 'custom'));

		return $options;
	}

	/**
	 * @return array
	 */
	public function getSeoDividerOptions()
	{
		$options = array(
			array(
				'label' => Craft::t('None'),
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

			array_push($options, array('optgroup' => Craft::t('Custom')));

			if (!array_key_exists($seoDivider, array('-' => 0, '•' => 1, '|' => 2, '/' => 3, ':' => 4)) && $seoDivider != '')
			{
				array_push($options, array('label' => $seoDivider, 'value' => $seoDivider));
			}
		}

		array_push($options, array('label' => Craft::t('Add Custom'), 'value' => 'custom'));

		return $options;
	}

	/**
	 * Returns keywords options
	 *
	 * @return array
	 */
	public function getKeywordsOptions($types = array("PlainText", 'RichText'))
	{
		$options        = array();
		$fields         = craft()->fields->getAllFields();
		$pluginSettings = craft()->plugins->getPlugin('sproutseo')->getSettings();

		$options[''] = Craft::t('None');

		$options[] = array('optgroup' => Craft::t('Generate from Existing Field'));

		foreach ($fields as $key => $field)
		{
			if (in_array($field->type, $types))
			{
				$context             = explode(":", $field->context);
				$context             = isset($context[0]) ? $context[0] : 'global';

				if ($pluginSettings->displayFieldHandles)
				{
					$options[$field->id] = $field->name . ' – {' . $field->handle . '}';
				}
				else
				{
					$options[$field->id] = $field->name;
				}
			}
		}

		$options[]           = array('optgroup' => Craft::t('Add Custom Field'));
		$options['manually'] = Craft::t('Display Editable Field');

		return $options;
	}

	/**
	 * Returns all plain fields available given a type
	 *
	 * @return array
	 */
	public function getOptimizedOptions($types = array("PlainText"), $handle = null, $settings = null)
	{
		$options        = array();
		$fields         = craft()->fields->getAllFields();
		$pluginSettings = craft()->plugins->getPlugin('sproutseo')->getSettings();

		$options[''] = Craft::t('None');
		$options[]   = array('optgroup' => Craft::t('Use Existing Field'));

		if ($handle == 'optimizedTitleField')
		{
			$options['elementTitle'] = Craft::t('Title');
		}

		foreach ($fields as $key => $field)
		{
			if (in_array($field->type, $types))
			{
				$context = explode(":", $field->context);
				$context = isset($context[0]) ? $context[0] : 'global';

				if ($pluginSettings->displayFieldHandles)
				{
					$options[$field->id] = $field->name . ' – {' . $field->handle . '}';
				}
				else
				{
					$options[$field->id] = $field->name;
				}
			}
		}

		$options[] = array('optgroup' => Craft::t('Add Custom Field'));

		$options['manually'] = Craft::t('Display Editable Field');

		$options[] = array('optgroup' => Craft::t('Define Custom Pattern'));

		if (!isset($options[$settings[$handle]]) && $settings[$handle] != 'manually')
		{
			$options[$settings[$handle]] = $settings[$handle];
		}

		$options['custom'] = Craft::t('Add Custom Format');

		return $options;
	}

	/**
	 * Returns all plain fields available given a type
	 *
	 * @return array
	 */
	public function getOptimizedTitleOptions($settings)
	{
		return $this->getOptimizedOptions(array('PlainText', 'RichText'), 'optimizedTitleField', $settings);
	}

	/**
	 * Returns all plain fields available given a type
	 *
	 * @return array
	 */
	public function getOptimizedDescriptionOptions($settings)
	{
		return $this->getOptimizedOptions(array('PlainText', 'RichText'), 'optimizedDescriptionField', $settings);
	}

	/**
	 * Returns all plain fields available given a type
	 *
	 * @return array
	 */
	public function getOptimizedAssetsOptions($settings)
	{
		return $this->getOptimizedOptions(array("Assets"), 'optimizedImageField', $settings);
	}

	/**
	 * @param $value
	 *
	 * @return array|null
	 */
	public function getCustomSettingFieldHandles($value)
	{
		// If there are no dynamic tags, just return the template
		if (strpos($value, '{') === false)
		{
			return null;
		}

		/**
		 *  {           - our pattern starts with an open bracket
		 *  <space>?    - zero or one space
		 *  (object\.)? - zero or one characters that spell "object."
		 *  (?<handles> - begin capture pattern and name it 'handles'
		 *  [a-zA-Z_]*  - any number of characters in Craft field handles
		 *  )           - end capture pattern named 'handles'
		 */
		preg_match_all('/{ ?(object\.)?(?<handles>[a-zA-Z_]*)/', $value, $matches);

		if (count($matches['handles']))
		{
			return array_unique($matches['handles']);
		}
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
				'label' => Craft::t('Select locale...')
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
		$schemaOptions = array('' => Craft::t('None'), array('optgroup' => Craft::t('Default Types')));

		$schemaOptions = array_merge($schemaOptions, array_map(function ($schema)
		{
			return array(
				'label' => $schema->getName(),
				'type'  => $schema->getType(),
				'value' => $schema->getUniqueKey()
			);
		}, $defaultSchema));

		if (count($customSchema))
		{
			array_push($schemaOptions, array('optgroup' => Craft::t('Custom Types')));

			$schemaOptions = array_merge($schemaOptions, array_map(function ($schema)
			{
				return array(
					'label'    => $schema->getName(),
					'type'     => $schema->getType(),
					'value'    => $schema->getUniqueKey(),
					'isCustom' => '1'
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

	/**
	 * @param $type
	 *
	 * @return array
	 */
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
			if (isset($schema['type']))
			{
				$type = $schema['type'];

				// Create a generic first item in our list that matches the top level schema
				// We do this so we don't have a blank dropdown option for our secondary schemas
				$firstItem = array(
					$type => array()
				);

				if (!isset($schema['isCustom']))
				{
					$values[$schema['value']] = $this->getSchemaChildren($type);

					if (count($values[$schema['value']]))
					{
						$values[$schema['value']] = array_merge($firstItem, $values[$schema['value']]);
					}
				}
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

	/**
	 * @param $type
	 * @param $metadataModel
	 *
	 * @return bool
	 */
	public function hasActiveMetadata($type, $metadataModel)
	{
		switch ($type)
		{
			case 'search':

				if (($metadataModel['optimizedTitle'] OR $metadataModel['title']) &&
					($metadataModel['optimizedDescription'] OR $metadataModel['description'])
				)
				{
					return true;
				}

				break;

			case 'openGraph':

				if (($metadataModel['optimizedTitle'] OR $metadataModel['title']) &&
					($metadataModel['optimizedDescription'] OR $metadataModel['description']) &&
					($metadataModel['optimizedImage'] OR $metadataModel['ogImage'])
				)
				{
					return true;
				}

				break;

			case 'twitterCard':

				if (($metadataModel['optimizedTitle'] OR $metadataModel['title']) &&
					($metadataModel['optimizedDescription'] OR $metadataModel['description']) &&
					($metadataModel['optimizedImage'] OR $metadataModel['twitterImage'])
				)
				{
					return true;
				}

				break;
		}

		return false;
	}

	/**
	 * Returns array of URL Enabled Section types and the name of Element ID associated with each
	 *
	 * @todo - rename this getElementIdName() or something like that
	 *
	 * @return array
	 */
	public function getVariableIdNames()
	{
		$registeredUrlEnabledSectionsTypes = craft()->plugins->call('registerSproutSeoUrlEnabledSectionTypes');

		$variableTypes = array();

		foreach ($registeredUrlEnabledSectionsTypes as $plugin => $urlEnabledSectionTypes)
		{
			foreach ($urlEnabledSectionTypes as $urlEnabledSectionType)
			{
				$idVariableName = $urlEnabledSectionType->getIdVariableName();
				array_push($variableTypes, $idVariableName);
			}
		}

		return $variableTypes;
	}
}
