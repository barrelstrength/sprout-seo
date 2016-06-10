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
			'displayPreview'         => array(AttributeType::Bool, 'default'=>true),
			'useElementTypeTitle'    => array(AttributeType::Bool, 'default'=>false),
			'usetMetaTitle'          => array(AttributeType::Bool, 'default'=>false),
			'useMetaDescription'     => array(AttributeType::Bool, 'default'=>false),
			'useMetaImage'           => array(AttributeType::Bool, 'default'=>false),
			'displayAdvancedOptions' => array(AttributeType::Bool, 'default'=>true),
			'showGeo'                => array(AttributeType::Bool, 'default'=>true),
			'showRobots'             => array(AttributeType::Bool, 'default'=>true),
			'showOpenGraph'          => array(AttributeType::Bool, 'default'=>true),
			'showTwitter'            => array(AttributeType::Bool, 'default'=>true),
		);
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('sproutseo/_fieldtypes/optimizeMeta/settings', array(
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

		$model = sproutSeo()->metaTags->getMetaTagContentByEntryId($entryId, $locale);

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
				sproutSeo()->metaTags->deleteMetaTagContentById($model->id);
			}

			return;
		}

		if (isset($_POST['fields']['sproutseo_fields']['robots']))
		{
			$fields['robots'] = SproutSeoOptimizeHelper::prepRobotsAsString($_POST['fields']['sproutseo_fields']['robots']);
		}

		// Add the entry ID to the field data we will submit for Sprout SEO
		$attributes['entryId'] = $entryId;
		$attributes['locale']  = $locale;

		// Grab all the other Sprout SEO fields.
		$attributes = array_merge($attributes, $fields);

		// Make sure all of our images are strings (twitter/og)
		// We need to do this in case another seo field with images exists
		$attributes['twitterImage'] = (!empty($attributes['twitterImage']) ? $attributes['twitterImage'][0] : null);
		$attributes['ogImage']      = (!empty($attributes['ogImage']) ? $attributes['ogImage'][0] : null);

		// Update or create our Meta Tag Content entry
		if ($model->entryId)
		{
			sproutSeo()->metaTags->updateMetaTagContent($model->id, $attributes);
		}
		else
		{
			sproutSeo()->metaTags->createMetaTagContent($attributes);
		}
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

		$values = sproutSeo()->metaTags->getMetaTagContentByEntryId($entryId, $locale);

		// Set up our asset fields
		if (isset($variables['values']->ogImage))
		{
			$asset                        = craft()->elements->getElementById($variables['values']->ogImage);
			$ogImageElements = array($asset);
		}
		else
		{
			$ogImageElements = array();
		}

		// Set up our asset fields
		if (isset($variables['values']->twitterImage))
		{
			$asset                             = craft()->elements->getElementById($variables['values']->twitterImage);
			$twitterImageElements = array($asset);
		}
		else
		{
			$twitterImageElements = array();
		}

		// Set assetsSourceExists
		$sources                         = craft()->assets->findFolders();
		$assetsSourceExists = count($sources);

		// Set elementType
		$elementType = craft()->elements->getElementType(ElementType::Asset);

		// Cleanup the namespace around the $name handle
		$name = str_replace("fields[", "", $name);
		$name = rtrim($name, "]");

		$fieldId = 'fields-' . $name . '-field';

		$name = "sproutseo_fields[$name]";

		$settings = $this->getSettings();

		return craft()->templates->render('sproutseo/_partials/fields/optimize', array(
			'name'   => $name,
			'values' => $values,
			'ogImageElements' => $ogImageElements,
			'twitterImageElements' => $twitterImageElements,
			'assetsSourceExists' => $assetsSourceExists,
			'elementType' => $elementType,
		  'fieldId' => $fieldId,
			'fieldContext' => 'field',
			'settings' => $settings
		));
	}
}