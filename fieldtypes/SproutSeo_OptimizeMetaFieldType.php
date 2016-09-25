<?php
namespace Craft;

class SproutSeo_OptimizeMetaFieldType extends BaseFieldType
{
	/**
	 * FieldType name
	 *
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Sprout SEO Optimize');
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
			'showBasicMeta'             => array(AttributeType::Bool, 'default' => false),
			'showOpenGraph'             => array(AttributeType::Bool, 'default' => false),
			'showTwitter'               => array(AttributeType::Bool, 'default' => false),
			'showGeo'                   => array(AttributeType::Bool, 'default' => false),
			'showRobots'                => array(AttributeType::Bool, 'default' => false),
		);
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('sproutseo/_fieldtypes/optimize/settings', array(
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
		$elementId = $this->element->id;

		$locale = $this->element->locale;

		$values = sproutSeo()->metadata->getMetadataContentByElementId($elementId, $locale);

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

		$values['robots'] = SproutSeoOptimizeHelper::prepRobotsForSettings($values->robots);

		// Set elementType
		$elementType = craft()->elements->getElementType(ElementType::Asset);

		// Cleanup the namespace around the $name handle
		$name = str_replace("fields[", "", $name);
		$name = rtrim($name, "]");

		$fieldId = 'fields-' . $name . '-field';

		$name = "sproutseo[metadata][$name]";

		$settings = $this->getSettings();

		return craft()->templates->render('sproutseo/_fieldtypes/optimize/input', array(
			'name'                 => $name,
			'values'               => $values,
			'ogImageElements'      => $ogImageElements,
			'twitterImageElements' => $twitterImageElements,
			'metaImageElements'    => $metaImageElements,
			'assetsSourceExists'   => $assetsSourceExists,
			'elementType'          => $elementType,
			'fieldId'              => $fieldId,
			'fieldContext'         => 'field',
			'settings'             => $settings
		));
	}

	/**
	 * Performs any additional actions after the element has been saved.
	 */
	public function onAfterElementSave()
	{
		$fields = craft()->request->getPost('fields.sproutseo.metadata');

		if (!isset($fields))
		{
			return;
		}

		$locale = $this->element->locale;

		// Get existing or new MetadataModel
		$model = sproutSeo()->metadata->getMetadataContentByElementId($this->element->id, $locale);

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
				sproutSeo()->metadata->deleteMetadataContentById($model->id);
			}

			return;
		}

		if (isset($fields['robots']))
		{
			$fields['robots'] = SproutSeoOptimizeHelper::getRobotsMetaValue($fields['robots']);
		}

		// Add the element ID to the field data we will submit for Sprout SEO
		$attributes['elementId'] = $this->element->id;
		$attributes['locale']  = $locale;

		// Grab all the other Sprout SEO fields.
		$attributes = array_merge($attributes, $fields);

		$settings   = $this->getSettings();
		$attributes = $this->processOptimizedTitle($attributes, $settings);
		$attributes = $this->processOptimizedDescription($attributes, $settings);
		$attributes = $this->processOptimizedFeatureImage($attributes, $settings);

		$model->setAttributes($attributes);

		$model = sproutSeo()->metadata->updateOptimizedAndAdvancedMetaValues($model);

		$columns = array_intersect_key($model->getAttributes(), $attributes);

		// Update or create our Meta Tag Content
		if ($model->id)
		{
			sproutSeo()->metadata->updateMetadataContent($model->id, $columns);
		}
		else
		{
			sproutSeo()->metadata->createMetadataContent($columns);
		}
	}

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

				$title = $this->getElementField($optimizedTitleFieldSetting);

				break;

			// Custom Value
			default:

				$title = craft()->templates->renderObjectTemplate($optimizedTitleFieldSetting, $this->element);

				break;
		}

		$attributes['optimizedTitle'] = $title;
		$attributes['title']          = $title;
		$attributes['ogTitle']        = $title;
		$attributes['twitterTitle']   = $title;

		return $attributes;
	}

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

				$description = $this->getElementField($optimizedDescriptionFieldSetting);

				break;

			// Custom Value
			default:

				$description = craft()->templates->renderObjectTemplate($optimizedDescriptionFieldSetting, $this->element);

				break;
		}

		$attributes['optimizedDescription'] = $description;
		$attributes['description']          = $description;
		$attributes['ogDescription']        = $description;
		$attributes['twitterDescription']   = $description;

		return $attributes;
	}

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

				$image = $this->getElementField($optimizedImageFieldSetting);

				break;
		}

		$attributes['optimizedImage'] = $image;
		$attributes['ogImage']        = $image;
		$attributes['twitterImage']   = $image;

		return $attributes;
	}

	private function getElementField($id)
	{
		$value = null;

		// it's a field id.
		if (is_numeric($id))
		{
			// Let's check if the field exists in the entry
			$field = craft()->fields->getFieldById($id);

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
			}
		}

		return $value;
	}
}