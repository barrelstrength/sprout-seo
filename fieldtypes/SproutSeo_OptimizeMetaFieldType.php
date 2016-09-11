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
			'displayPreview'            => array(AttributeType::Bool, 'default' => true),
			'showGeo'                   => array(AttributeType::Bool, 'default' => true),
			'showRobots'                => array(AttributeType::Bool, 'default' => true),
			'showOpenGraph'             => array(AttributeType::Bool, 'default' => true),
			'showTwitter'               => array(AttributeType::Bool, 'default' => true)
		);
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('sproutseo/_fieldtypes/optimize/settings', array(
			'settings' => $this->getSettings()
		));
	}

	/**
	 * Performs any additional actions after the element has been saved.
	 */
	public function onAfterElementSave()
	{
		// grab only the basic fields
		$fields = (isset($_POST['fields']['sproutseo_fields'])) ? $_POST['fields']['sproutseo_fields'] : null;

		if (!isset($fields))
		{
			return;
		}

		$entryId = (isset($_POST['entryId']) && $_POST['entryId'] != "")
			? $_POST['entryId']
			: $this->element->id;

		$locale = $this->element->locale;

		$model = sproutSeo()->metadata->getMetaTagContentByEntryId($entryId, $locale);

		// Test to see if we have any values in our Sprout SEO fields
		$saveSproutSeoFields = false;
		foreach ($_POST['fields']['sproutseo_fields'] as $key => $value)
		{
			if ($value)
			{
				$saveSproutSeoFields = true;
				continue;
			}
		}

		// If we don't have any values in our Sprout SEO fields
		// don't add a record to the database
		// but if a record already exists, we also should delete it.
		if (!$saveSproutSeoFields)
		{
			// Remove record since it is now blank
			if ($model->id)
			{
				sproutSeo()->metadata->deleteMetaTagContentById($model->id);
			}

			return;
		}

		if (isset($fields['robots']))
		{
			$fields['robots'] = SproutSeoOptimizeHelper::getRobotsMetaValue($fields['robots']);
		}

		// Add the entry ID to the field data we will submit for Sprout SEO
		$attributes['entryId'] = $entryId;
		$attributes['locale']  = $locale;

		// Grab all the other Sprout SEO fields.
		$attributes = array_merge($attributes, $fields);

		// Make sure all of our images are strings (twitter/og)
		// We need to do this in case another seo field with images exists
		$attributes['optimizedImage'] = (!empty($attributes['optimizedImage']) ? $attributes['optimizedImage'][0] : null);
		$attributes['ogImage']        = (!empty($attributes['ogImage']) ? $attributes['ogImage'][0] : null);
		$attributes['twitterImage']   = (!empty($attributes['twitterImage']) ? $attributes['twitterImage'][0] : null);

		// Validate any setting of the field type
		$settings = $this->getSettings();

		// Title - validations begins
		$title = null;

		if ($settings['optimizedTitleField'] == 'manually' && $attributes['title'])
		{
			$attributes['ogTitle']      = $attributes['title'];
			$attributes['twitterTitle'] = $attributes['title'];
		}
		else
		{
			if ($settings['optimizedTitleField'] != 'manually')
			{
				//it's an field id.
				if (is_numeric($settings['optimizedTitleField']))
				{
					$title = $this->_getElementField($settings['optimizedTitleField']);
				}
				else
				{
					// @todo - why do we need to test this against a string 'element-title'?
					if ($settings['optimizedTitleField'] == 'element-title' && $entryId)
					{
						$entry = craft()->elements->getElementById($entryId);

						if ($entry)
						{
							$title = $entry->title;
						}
					}
					else
					{
						$title = craft()->templates->renderObjectTemplate($settings['optimizedTitleField'], $this->element);
					}
				}
			}
		}

		$attributes['title']          = $title;
		$attributes['optimizedTitle'] = $title;
		// Title - validations ends

		// Description - validations begins
		$ogDescription      = null;
		$twitterDescription = null;

		// @todo - we should probably be using $attributes['optimizedDescription'] here
		if ($settings['optimizedDescriptionField'] == 'manually' && $attributes['description'])
		{
			$ogDescription      = $attributes['description'];
			$twitterDescription = $attributes['description'];
		}
		else
		{
			if ($settings['optimizedDescriptionField'] != 'manually')
			{
				//it's an field id.
				if (is_numeric($settings['optimizedDescriptionField']))
				{
					$ogDescription      = $this->_getElementField($settings['optimizedDescriptionField']);
					$twitterDescription = $ogDescription;
				}
				//it's a custom value
				else
				{
					$ogDescription      = craft()->templates->renderObjectTemplate($settings['optimizedDescriptionField'], $this->element);
					$twitterDescription = craft()->templates->renderObjectTemplate($settings['optimizedDescriptionField'], $this->element);
				}
			}
		}

		$attributes['ogDescription']      = $ogDescription;
		$attributes['twitterDescription'] = $twitterDescription;
		$attributes['description']        = $ogDescription != null ? $ogDescription : null;
		// Description - validations ends

		// Image Field - validations begins
		$metaImage = null;
		if ($settings['optimizedImageField'] == 'manually' && $attributes['optimizedImage'])
		{
			$metaImage = $attributes['optimizedImage'];
		}
		else
		{
			if ($settings['optimizedImageField'] != 'manually')
			{
				$metaImage = $this->_getElementField($settings['optimizedImageField']);
			}
		}

		$attributes['optimizedImage'] = $metaImage;
		$attributes['ogImage']        = $metaImage;
		$attributes['twitterImage']   = $metaImage;
		// Image Field - validations ends

		// Update or create our Meta Tag Content entry
		if ($model->entryId)
		{
			sproutSeo()->metadata->updateMetaTagContent($model->id, $attributes);
		}
		else
		{
			sproutSeo()->metadata->createMetaTagContent($attributes);
		}
	}

	private function _getElementField($id)
	{
		$value = null;
		//it's an field id.
		if (is_numeric($id))
		{
			//Let's check if the field exists in the entry
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
		$entryId = $this->element->id;

		$locale = $this->element->locale;

		$values = sproutSeo()->metadata->getMetaTagContentByEntryId($entryId, $locale);

		$ogImageElements      = array();
		$metaImageElements    = array();
		$twitterImageElements = array();

		// Set up our asset fields
		if (isset($values->ogImage))
		{
			$asset           = craft()->elements->getElementById($values->ogImage);
			$ogImageElements = array($asset);
		}

		if (isset($values->metaImage))
		{
			$asset             = craft()->elements->getElementById($values->metaImage);
			$metaImageElements = array($asset);
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

		$name = "sproutseo_fields[$name]";

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
}