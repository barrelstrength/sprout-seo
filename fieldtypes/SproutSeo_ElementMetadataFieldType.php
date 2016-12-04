<?php
namespace Craft;

class SproutSeo_ElementMetadataFieldType extends BaseFieldType implements IPreviewableFieldType
{
	/**
	 * Our active Metadata
	 *
	 * @var SproutSeo_MetadataModel
	 */
	public $metadata;

	/**
	 * An array of our metadata values to use for
	 * processing, validation, and handing off to the db
	 * We keep these separate from the supported $value parameter
	 * as the $value parameter helps managed handing back values
	 * after failed validation scenarios
	 *
	 * @var array()
	 */
	public $values;

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
			'requiredTitle'             => array(AttributeType::Bool, 'default' => true),
			'requiredDescription'       => array(AttributeType::Bool, 'default' => true),
			'requiredImage'             => array(AttributeType::Bool, 'default' => false),
		);
	}

	/**
	 * @param mixed $value
	 *
	 * @return
	 */
	public function prepValue($value)
	{
		// Grab our values from the db
		$elementId = $this->element->id;
		$locale    = $this->element->locale;
		$values    = sproutSeo()->elementMetadata->getElementMetadataByElementId($elementId, $locale);

		// $value will be an array if there was a validation error or we're loading a draft/version.
		// If we have a value, we are probably loading a Draft or Invalid Entry so let's override any
		// of those values. We need to undo a few things about how the Draft data gets stored so
		// that it gets reprocessed properly
		if (is_array($value))
		{
			$existingValues = SproutSeo_MetadataModel::populateModel($value['metadata']);

			return $this->prepareExistingValuesForPage($values, $existingValues);
		}

		// For the CP, return a SproutSeo_MetadataModel
		return $values;
	}

	/**
	 * @param mixed $value
	 *
	 * @return string
	 */
	public function getTableAttributeHtml($value)
	{
		craft()->templates->includeCssResource('sproutseo/css/sproutseo.css');

		$html = craft()->templates->render('sproutseo/_includes/metadata-status-icons', array(
			'sectionMetadata' => $value
		));

		return $html;
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
		$inputId            = craft()->templates->formatInputId($name);
		$namespaceInputName = craft()->templates->namespaceInputName($inputId);
		$namespaceInputId   = craft()->templates->namespaceInputId($inputId);

		$ogImageElements      = array();
		$metaImageElements    = array();
		$twitterImageElements = array();

		// Set up our asset fields
		if (isset($value->optimizedImage))
		{
			// If validation fails, we need to make sure our asset is just an ID
			if (is_array($value->optimizedImage))
			{
				$value->optimizedImage = $value->optimizedImage[0];
			}

			$asset             = craft()->elements->getElementById($value->optimizedImage);
			$metaImageElements = array($asset);
		}

		if (isset($value->ogImage))
		{
			$asset           = craft()->elements->getElementById($value->ogImage);
			$ogImageElements = array($asset);
		}

		if (isset($value->twitterImage))
		{
			$asset                = craft()->elements->getElementById($value->twitterImage);
			$twitterImageElements = array($asset);
		}

		// Set assetsSourceExists
		$sources            = craft()->assets->findFolders();
		$assetsSourceExists = count($sources);

		$value['robots'] = SproutSeoOptimizeHelper::prepareRobotsMetadataForSettings($value->robots);

		// Set elementType
		// @todo - rename this variable, it is specific for Assets
		$elementType = craft()->elements->getElementType(ElementType::Asset);

		// Cleanup the namespace around the $name handle
		$name = str_replace("fields[", "", $name);
		$name = rtrim($name, "]");

		$fieldId = 'fields-' . $name . '-field';

		$name = "sproutseo[metadata][$name]";

		$settings = $this->getSettings();

		/**
		 * Get the prioritized metadata at this level so we can use it as placeholder text
		 *
		 * @var SproutSeoBaseUrlEnabledSectionType $urlEnabledSectionType
		 */
		$urlEnabledSectionType = sproutSeo()->sectionMetadata->getUrlEnabledSectionTypeByElementType($this->element->getElementType());

		$urlEnabledSectionType->typeIdContext = 'matchedElementCheck';

		$urlEnabledSectionIdColumnName = $urlEnabledSectionType->getIdColumnName();
		$type                          = $urlEnabledSectionType->getId();
		$urlEnabledSectionId           = $this->element->{$urlEnabledSectionIdColumnName};
		$urlEnabledSection             = $urlEnabledSectionType->urlEnabledSections[$type . '-' . $urlEnabledSectionId];

		sproutSeo()->optimize->globals                    = sproutSeo()->globalMetadata->getGlobalMetadata();
		sproutSeo()->optimize->urlEnabledSection          = $urlEnabledSection;
		sproutSeo()->optimize->urlEnabledSection->element = $this->element;

		$prioritizedMetadata = sproutSeo()->optimize->getPrioritizedMetadataModel();

		// @todo - what are the ogImageElements, twitterImageElements, etc being used for?
		// they don't appear to be used in the elementdata/input template...
		return craft()->templates->render('sproutseo/_fieldtypes/elementmetadata/input', array(
			'name'                 => $name,
			'namespaceInputName'   => $namespaceInputName,
			'namespaceInputId'     => $namespaceInputId,
			'pluginTemplate'       => 'sproutseo',
			'values'               => $value,
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
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function prepValueFromPost($value)
	{
		if (!isset($value['metadata']))
		{
			return $value;
		}

		$metadata = $value['metadata'];

		$this->values = $this->getMetadataFieldValues($metadata);

		return $value;
	}

	/**
	 * @param mixed $value
	 *
	 * @return true|string|array
	 */
	public function validate($value)
	{
		$fieldHandle = $this->model->handle;
		$isRequired  = $this->element->getContent()->isAttributeRequired($fieldHandle);

		if (!$isRequired)
		{
			return true;
		}

		$optimizedTitle       = $this->getSettings()->optimizedTitleField;
		$optimizedDescription = $this->getSettings()->optimizedDescriptionField;
		$optimizedImage       = $this->getSettings()->optimizedImageField;

		if ($optimizedTitle != 'manually' &&
			  $optimizedDescription != 'manually' &&
			  $optimizedImage != 'manually')
		{
			return true;
		}

		$errorMessage = array();

		$requiredTitle       = $this->getSettings()->requiredTitle;
		$requiredDescription = $this->getSettings()->requiredDescription;
		$requiredImage       = $this->getSettings()->requiredImage;

		if ($requiredTitle && $optimizedTitle == 'manually' && empty($this->values['optimizedTitle']))
		{
			$errorMessage[] = Craft::t("Meta Title field cannot be blank.");
		}

		if ($requiredDescription && $optimizedDescription == 'manually' && empty($this->values['optimizedDescription']))
		{
			$errorMessage[] = Craft::t("Meta Description field cannot be blank.");
		}

		if (!$requiredImage && $optimizedImage == 'manually' && empty($this->values['optimizedImage']))
		{
			$errorMessage[] = Craft::t("Meta Image field cannot be blank.");
		}

		return count($errorMessage) ? $errorMessage : true;
	}

	public function onAfterSave()
	{
		sproutSeo()->elementMetadata->resaveElementsIfUsingElementMetadataField($this->model->id);
	}

	/**
	 * Save metadata to the sproutseo_metadata_elements table
	 */
	public function onAfterElementSave()
	{
		$fieldHandle = $this->model->handle;
		$fields      = $this->element->getContent()->{$fieldHandle}['metadata'];

		$this->values = $this->getMetadataFieldValues($fields);

		if ($this->model->id)
		{
			sproutSeo()->elementMetadata->updateElementMetadata($this->metadata->id, $this->values);
		}
		else
		{
			sproutSeo()->elementMetadata->createElementMetadata($this->values);
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
				$fiveKeywords = array_slice($rakeKeywords, 0, 5);
				$keywords     = implode(',', $fiveKeywords);

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

		// Just save the first 255 characters (we only output 160...)
		$description                        = substr(trim($description), 0, 255);
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

	/**
	 * @param $attributes
	 * @param $settings
	 *
	 * @return mixed
	 */
	protected function processMainEntity($attributes, $settings)
	{
		if (!isset($attributes['schemaOverrideTypeId']))
		{
			$attributes['schemaOverrideTypeId'] = null;
		}

		return $attributes;
	}

	/**
	 * Make sure our Meta Details blocks behave as we need them to.
	 *
	 * Can be triggered via:
	 * - Save Element
	 * - ResaveElements via saving of Element Metadata Field
	 * - ResaveElements via save Field Layout
	 *
	 * Handles several scenarios:
	 * - New Metadata, Existing Metadata
	 * - Meta Details Blocks - enabled, partially enabled, disabled
	 *
	 * @param $attributes
	 * @param $settings
	 *
	 * @return mixed
	 */
	protected function processMetaDetails($attributes, $settings)
	{
		if (isset($attributes['customizationSettings']))
		{
			$attributes['customizationSettings'] = json_encode($attributes['customizationSettings']);
		}
		else
		{
			$details = SproutSeoOptimizeHelper::getDefaultCustomizationSettings();

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

	protected function getMetadataFieldValues($fields)
	{
		$locale      = $this->element->locale;
		$settings    = $this->getSettings();

		// Get instance of our Element Metadata model if a call comes from a ResaveElements task
		// Get existing or new MetadataModel
		$this->metadata = sproutSeo()->elementMetadata->getElementMetadataByElementId($this->element->id, $locale);

		// Add the element ID to the field data we will submit for Sprout SEO
		$attributes['elementId'] = $this->element->id;
		$attributes['locale']    = $locale;

		// Grab all the other Sprout SEO fields.
		if ($fields)
		{
			if (isset($fields['robots']))
			{
				$fields['robots'] = SproutSeoOptimizeHelper::prepareRobotsMetadataValue($fields['robots']);
			}

			$attributes = array_merge($attributes, $fields);
		}
		else
		{
			// Make sure we have some default values in place
			$attributes = $this->metadata->getAttributes();

			$attributes['customizationSettings'] = json_decode($this->metadata->customizationSettings);

			// @todo - this is excessive. Refactor how customizationSettings works.
			unset($attributes['isNew']);
			unset($attributes['default']);
			unset($attributes['name']);
			unset($attributes['handle']);
			unset($attributes['hasUrls']);
			unset($attributes['url']);
			unset($attributes['priority']);
			unset($attributes['changeFrequency']);
			unset($attributes['urlEnabledSectionId']);
			unset($attributes['isCustom']);
			unset($attributes['type']);
			unset($attributes['enabled']);
			unset($attributes['appendTitleValue']);
			unset($attributes['position']);
			unset($attributes['ogImageSecure']);
			unset($attributes['ogImageWidth']);
			unset($attributes['ogImageHeight']);
			unset($attributes['ogImageType']);
			unset($attributes['ogDateUpdated']);
			unset($attributes['ogDateCreated']);
			unset($attributes['ogExpiryDate']);

			// @todo - remove this once we simplify. Add these values back here
			// since we overwrite them when we call getAttributes above.
			$attributes['elementId'] = $this->element->id;
			$attributes['locale']    = $locale;
		}

		// Meta Details needs to go first
		$attributes = $this->processMetaDetails($attributes, $settings);
		$attributes = $this->processOptimizedTitle($attributes, $settings);
		$attributes = $this->processOptimizedDescription($attributes, $settings);
		$attributes = $this->processOptimizedKeywords($attributes, $settings);
		$attributes = $this->processOptimizedFeatureImage($attributes, $settings);
		$attributes = $this->processMainEntity($attributes, $settings);

		$this->metadata->setAttributes($attributes);

		$this->metadata = SproutSeoOptimizeHelper::updateOptimizedAndAdvancedMetaValues($this->metadata);

		// Overwrite any values we have from our existing model with the values from our attributes
		return array_intersect_key($this->metadata->getAttributes(), $attributes);
	}

	/**
	 * @param $values
	 * @param $existingValues
	 *
	 * @return mixed
	 */
	protected function prepareExistingValuesForPage($values, $existingValues)
	{
		foreach ($values->getAttributes() as $key => $value)
		{
			// Test for a value on each of our models in their order of priority
			if ($existingValues->getAttribute($key))
			{
				$values[$key] = $existingValues[$key];
			}

			if (($key == 'ogImage' OR $key == 'twitterImage') AND count($values[$key]))
			{
				$values[$key] = $values[$key][0];
			}

			if ($key == 'robots')
			{
				$values[$key] = SproutSeoOptimizeHelper::prepareRobotsMetadataValue($values[$key]);
			}

			if ($key == 'customizationSettings')
			{
				$values[$key] = json_encode($values[$key]);
			}
		}

		return $values;
	}
}