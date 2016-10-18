<?php
namespace Craft;

class SproutSeo_ElementMetadataFieldType extends BaseFieldType
{
	/**
	 * FieldType name
	 *
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Element Metadata');
	}

	/**
	 * Define database column
	 *
	 * @return false
	 */
	public function defineContentAttribute()
	{
		// We don't need a column in the content table
		return false;
	}

	/**
	 * @return array
	 */
	protected function defineSettings()
	{
		return array(
			'optimizedTitleField'       => array(AttributeType::String),
			'optimizedDescriptionField' => array(AttributeType::String),
			'optimizedImageField'       => array(AttributeType::String),
			'optimizedKeywordsField'    => array(AttributeType::String),
			'displayPreview'            => array(AttributeType::Bool, 'default' => true),
			'showMainEntity'            => array(AttributeType::Bool, 'default' => false),
			'showSearchMeta'            => array(AttributeType::Bool, 'default' => false),
			'showOpenGraph'             => array(AttributeType::Bool, 'default' => false),
			'showTwitter'               => array(AttributeType::Bool, 'default' => false),
			'showGeo'                   => array(AttributeType::Bool, 'default' => false),
			'showRobots'                => array(AttributeType::Bool, 'default' => false),
		);
	}

	/**
	 * @return string
	 */
	public function getSettingsHtml()
	{
		return craft()->templates->render('sproutseo/_fieldtypes/elementmetadata/settings', array(
			'settings' => $this->getSettings()
		));
	}

	public function prepValue($value)
	{
		$globals  = sproutSeo()->optimize->globals;
		$identity = $globals['identity'];
		$schema   = new SproutSeo_WebsiteIdentityWebsiteSchema();

		if ($identityType = $identity['@type'])
		{
			$schemaModel = 'Craft\SproutSeo_WebsiteIdentity' . $identityType . 'Schema';
			$schema      = new $schemaModel();
		}

		$schema->addContext               = true;
		$schema->globals                  = sproutSeo()->optimize->globals;
		$schema->element                  = $this->element;
		$schema->prioritizedMetadataModel = sproutSeo()->optimize->prioritizedMetadataModel;

		return $schema;
	}

	/**
	 * Display our FieldType
	 *
	 * @param string $name   Our FieldType handle
	 * @param string $value  Always returns blank, our block
	 *                       only styles the Instructions field
	 *
	 * @return string Return our blocks input template
	 */
	public function getInputHtml($name, $value)
	{
		$elementId = $this->element->id;

		$locale = $this->element->locale;

		$values = sproutSeo()->elementMetadata->getElementMetadataByElementId($elementId, $locale);

		$ogImageElements      = array();
		$metaImageElements    = array();
		$twitterImageElements = array();

		// Set up our asset fields
		if (isset($values->optimizedImage))
		{
			$asset             = craft()->elements->getElementById($values->optimizedImage);
			$metaImageElements = array($asset);
		}

		if (isset($values->ogImage))
		{
			$asset           = craft()->elements->getElementById($values->ogImage);
			$ogImageElements = array($asset);
		}

		if (isset($values->twitterImage))
		{
			$asset                = craft()->elements->getElementById($values->twitterImage);
			$twitterImageElements = array($asset);
		}

		// Set assetsSourceExists
		$sources            = craft()->assets->findFolders();
		$assetsSourceExists = count($sources);

		$values['robots'] = SproutSeoOptimizeHelper::prepareRobotsMetadataForSettings($values->robots);

		// Set elementType
		$elementType = craft()->elements->getElementType(ElementType::Asset);

		// Cleanup the namespace around the $name handle
		$name = str_replace("fields[", "", $name);
		$name = rtrim($name, "]");

		$fieldId = 'fields-' . $name . '-field';

		$name = "sproutseo[metadata][$name]";

		$settings = $this->getSettings();

		// Get the prioritized metadata at this level so we can use it as placeholder text
		/**
		 * @var SproutSeoBaseUrlEnabledSectionType $urlEnabledSectionType
		 */
		$urlEnabledSectionType = sproutSeo()->sectionMetadata->getUrlEnabledSectionTypeByElementType($this->element->getElementType());

		$urlEnabledSectionType->typeIdContext = 'matchedElementCheck';

		$urlEnabledSectionIdColumnName = $urlEnabledSectionType->getIdColumnName();
		$type                          = $urlEnabledSectionType->getId();
		$urlEnabledSectionId           = $this->element->{$urlEnabledSectionIdColumnName};
		$urlEnabledSection             = $urlEnabledSectionType->urlEnabledSections[$type . '-' . $urlEnabledSectionId];

		sproutSeo()->optimize->globals           = sproutSeo()->globalMetadata->getGlobalMetadata();
		sproutSeo()->optimize->urlEnabledSection = $urlEnabledSection;

		$prioritizedMetadata = sproutSeo()->optimize->getPrioritizedMetadataModel();

		// @todo - what are the ogImageElements, twitterImageElements, etc being used for?
		// they don't appear to be used in the elementdata/input template...
		return craft()->templates->render('sproutseo/_fieldtypes/elementmetadata/input', array(
			'name'                 => $name,
			'values'               => $values,
			'ogImageElements'      => $ogImageElements,
			'twitterImageElements' => $twitterImageElements,
			'metaImageElements'    => $metaImageElements,
			'assetsSourceExists'   => $assetsSourceExists,
			'elementType'          => $elementType,
			'fieldId'              => $fieldId,
			'fieldContext'         => 'field',
			'settings'             => $settings,
			'prioritizedMetadata'  => $prioritizedMetadata,
			'elementHandle'        => $this->model->handle
		));
	}

	/**
	 * Performs any additional actions after the element has been saved.
	 */
	public function onAfterElementSave()
	{
		$fieldHandle = $this->model->handle;
		$fields      = $this->element->getContent()->{$fieldHandle}['metadata'];
		$locale      = $this->element->locale;
		// Instance model if call comes from ResaveElements task
		// Get existing or new MetadataModel
		$model = sproutSeo()->elementMetadata->getElementMetadataByElementId($this->element->id, $locale);

		if ($fields)
		{
			// Test to see if we have any values in our Sprout SEO fields
			$saveSproutSeoFields = false;

			foreach ($fields as $key => $value)
			{
				if ($value)
				{
					$saveSproutSeoFields = true;
					continue;
				}
			}

			// If we don't have any values in our Sprout SEO fields
			// don't add a record to the database
			// If a record already exists, we should delete it.
			if (!$saveSproutSeoFields)
			{
				// Remove record since it is now blank
				if ($model->id)
				{
					sproutSeo()->elementMetadata->deleteElementMetadataById($model->id);
				}

				return;
			}

			if (isset($fields['robots']))
			{
				$fields['robots'] = SproutSeoOptimizeHelper::prepareRobotsMetadataValue($fields['robots']);
			}
		}
		else
		{
			$attributes['optimizedTitle']       = null;
			$attributes['optimizedDescription'] = null;
			$attributes['optimizedImage']       = null;
			$attributes['elementId']            = null;
		}

		// Add the element ID to the field data we will submit for Sprout SEO
		$attributes['elementId'] = $this->element->id;
		$attributes['locale']    = $locale;

		// Grab all the other Sprout SEO fields.
		if ($fields)
		{
			$attributes = array_merge($attributes, $fields);
		}

		$settings = $this->getSettings();
		// meta details needs go first
		$attributes = $this->processMetaDetails($attributes, $settings);
		$attributes = $this->processOptimizedTitle($attributes, $settings);
		$attributes = $this->processOptimizedDescription($attributes, $settings);
		$attributes = $this->processOptimizedKeywords($attributes, $settings);
		$attributes = $this->processOptimizedFeatureImage($attributes, $settings);
		$attributes = $this->processMainEntity($attributes, $settings);

		$model->setAttributes($attributes);

		$model = SproutSeoOptimizeHelper::updateOptimizedAndAdvancedMetaValues($model);

		$columns = array_intersect_key($model->getAttributes(), $attributes);

		if ($model->id)
		{
			sproutSeo()->elementMetadata->updateElementMetadata($model->id, $columns);
		}
		else
		{
			sproutSeo()->elementMetadata->createElementMetadata($columns);
		}
	}

	/**
	 * @param $attributes
	 * @param $settings
	 *
	 * @return mixed
	 */
	protected function processOptimizedTitle($attributes, $settings)
	{
		$title = null;

		$optimizedTitleFieldSetting = $settings['optimizedTitleField'];

		switch (true)
		{
			// Element Title
			case ($optimizedTitleFieldSetting == 'elementTitle' && $this->element->id):

				$title = $this->element->title;

				break;

			// Manual Title
			case ($optimizedTitleFieldSetting == 'manually'):

				$title = ($attributes['optimizedTitle']) ? $attributes['optimizedTitle'] : null;

				break;

			// Custom Field
			case (is_numeric($optimizedTitleFieldSetting)):

				$title = $this->getSelectedFieldForOptimizedMetadata($optimizedTitleFieldSetting);

				break;

			// Custom Value
			default:

				$title = craft()->templates->renderObjectTemplate($optimizedTitleFieldSetting, $this->element);

				break;
		}

		$attributes['optimizedTitle'] = $title;

		$attributes = $this->setMetaDetailsValues('title', $title, $attributes);

		return $attributes;
	}

	private function setMetaDetailsValues($type, $value, $attributes)
	{
		$metaDetails = JsonHelper::decode($attributes['customizationSettings']);

		$ogKey        = 'og' . ucfirst($type);
		$twitterKey   = 'twitter' . ucfirst($type);
		$ogValue      = isset($attributes[$ogKey]) ? $attributes[$ogKey] : null;
		$twitterValue = isset($attributes[$twitterKey]) ? $attributes[$twitterKey] : null;
		$searchValue  = isset($attributes[$type]) ? $attributes[$type] : null;

		// Default values
		$attributes[$type]       = $value;
		$attributes[$ogKey]      = $value;
		$attributes[$twitterKey] = $value;

		if (isset($metaDetails['searchMetaSectionMetadataEnabled']) && $metaDetails['searchMetaSectionMetadataEnabled'] && $searchValue)
		{
			$attributes[$type] = $searchValue;
		}

		if (isset($metaDetails['openGraphSectionMetadataEnabled']) && $metaDetails['openGraphSectionMetadataEnabled'] && $ogValue)
		{
			$attributes[$ogKey] = $ogValue;
		}

		if (isset($metaDetails['twitterCardSectionMetadataEnabled']) && $metaDetails['twitterCardSectionMetadataEnabled'] && $twitterValue)
		{
			$attributes[$twitterKey] = $twitterValue;
		}

		return $attributes;
	}

	/**
	 * @param $attributes
	 * @param $settings
	 *
	 * @return mixed
	 */
	protected function processOptimizedKeywords($attributes, $settings)
	{
		$keywords = null;

		$optimizedKeywordsFieldSetting = $settings['optimizedKeywordsField'];

		switch (true)
		{
			// Manual Keywords
			case ($optimizedKeywordsFieldSetting == 'manually'):

				$keywords = ($attributes['optimizedKeywords']) ? $attributes['optimizedKeywords'] : null;

				break;

			// Auto-generate keywords from target field
			case (is_numeric($optimizedKeywordsFieldSetting)):

				$keywords     = $this->getSelectedFieldForOptimizedMetadata($optimizedKeywordsFieldSetting);
				$rake         = new Rake();
				$rakeKeywords = array_keys($rake->extract($keywords));
				$keywords     = implode(',', $rakeKeywords);

				break;
		}

		$attributes['optimizedKeywords'] = $keywords;

		return $attributes;
	}

	/**
	 * @param $attributes
	 * @param $settings
	 *
	 * @return mixed
	 */
	protected function processOptimizedDescription($attributes, $settings)
	{
		$description = null;

		$optimizedDescriptionFieldSetting = $settings['optimizedDescriptionField'];

		switch (true)
		{
			// Manual Description
			case ($optimizedDescriptionFieldSetting == 'manually'):

				$description = ($attributes['optimizedDescription']) ? $attributes['optimizedDescription'] : null;

				break;

			// Custom Description
			case (is_numeric($optimizedDescriptionFieldSetting)):

				$description = $this->getSelectedFieldForOptimizedMetadata($optimizedDescriptionFieldSetting);

				break;

			// Custom Value
			default:

				$description = craft()->templates->renderObjectTemplate($optimizedDescriptionFieldSetting, $this->element);

				break;
		}

		$attributes['optimizedDescription'] = $description;
		$attributes                         = $this->setMetaDetailsValues('description', $description, $attributes);

		return $attributes;
	}

	/**
	 * @param $attributes
	 * @param $settings
	 *
	 * @return mixed
	 */
	protected function processOptimizedFeatureImage($attributes, $settings)
	{
		$image = null;

		$optimizedImageFieldSetting = $settings['optimizedImageField'];

		switch (true)
		{
			// Manual Image
			case ($optimizedImageFieldSetting == 'manually'):

				$image = !empty($attributes['optimizedImage']) ? $attributes['optimizedImage'][0] : null;

				break;

			// Custom Image Field
			case (is_numeric($optimizedImageFieldSetting)):

				$image = $this->getSelectedFieldForOptimizedMetadata($optimizedImageFieldSetting);

				break;
		}

		$attributes['optimizedImage'] = $image;
		$attributes['ogImage']        = $image;
		$attributes['twitterImage']   = $image;

		return $attributes;
	}

	protected function processMainEntity($attributes, $settings)
	{
		if (!isset($attributes['schemaOverrideTypeId']))
		{
			$attributes['schemaOverrideTypeId'] = null;
		}

		return $attributes;
	}

	protected function processMetaDetails($attributes, $settings)
	{
		$details = array();

		if (!isset($attributes['customizationSettings']))
		{
			if ($settings['showSearchMeta'])
			{
				$details['searchMetaSectionMetadataEnabled'] = 0;
			}

			if ($settings['showOpenGraph'])
			{
				$details['openGraphSectionMetadataEnabled'] = 0;
			}

			if ($settings['showTwitter'])
			{
				$details['twitterCardSectionMetadataEnabled'] = 0;
			}

			if ($settings['showGeo'])
			{
				$details['geoSectionMetadataEnabled'] = 0;
			}

			if ($settings['showRobots'])
			{
				$details['robotsSectionMetadataEnabled'] = 0;
			}

			$attributes['customizationSettings'] = json_encode($details);
		}

		return $attributes;
	}

	/**
	 * @param $fieldId
	 *
	 * @return null
	 */
	private function getSelectedFieldForOptimizedMetadata($fieldId)
	{
		$value = null;

		if (is_numeric($fieldId))
		{
			// Does the field exist on the element?
			$field = craft()->fields->getFieldById($fieldId);

			if ($field)
			{
				if (isset($_POST['fields'][$field->handle]))
				{
					if ($field->type == 'Assets')
					{
						$value = (!empty($_POST['fields'][$field->handle]) ? $_POST['fields'][$field->handle][0] : null);
					}
					else
					{
						$value = $_POST['fields'][$field->handle];
					}
				}
				//Resave elements
				else
				{
					if (isset($this->element->{$field->handle}))
					{
						$elementValue = $this->element->{$field->handle};

						if ($field->type == 'Assets')
						{
							$value = (isset($elementValue[0]->id) ? $elementValue[0]->id : null);
						}
						else
						{
							$value = $elementValue;
						}
					}
				}
			}
		}

		return $value;
	}
}