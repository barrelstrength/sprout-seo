<?php
namespace Craft;

class SproutSeo_OptimizeService extends BaseApplicationComponent
{
	protected $templateMeta = array();
	private $context;

	protected $siteInfo;
	protected $divider;
	protected $currentUrl;

	/**
	 * Store our template meta data in a place so we can access when we need to
	 *
	 * @return array
	 */
	public function getMetaTagsFromTemplate($type = null)
	{
		$entry = isset($this->context['entry']) ? $this->context['entry'] : null;

		switch ($type)
		{
			case 'metaTagsGroup':
				if($entry)
				{
					$slug = $entry->slug;

					$this->templateMeta = array('slug'=>$slug);
				}
			break;
			case 'entry':
				if($entry)
				{
					//this will support any element
					$this->templateMeta = array('entryId'=>$entry->id);
				}
			break;
		}

		return $this->templateMeta;
	}

	/**
	 * Add values to the master $this->templateMeta array
	 *
	 * @param $meta
	 */
	public function updateMeta($meta)
	{
		if (count($meta))
		{
			foreach ($meta as $key => $value)
			{
				$this->templateMeta[$key] = $value;
			}
		}
	}

	/**
	 * @param $overrideInfo
	 *
	 * @return string
	 */
	public function optimize()
	{
		$prioritizedMetaTagModel = $this->getOptimizedMeta();

		craft()->templates->setTemplatesPath(craft()->path->getPluginsPath());

		$output = craft()->templates->render('sproutseo/templates/_special/meta', array(
			'meta' => $prioritizedMetaTagModel->getMetaTagData()
		));

		craft()->templates->setTemplatesPath(craft()->path->getSiteTemplatesPath());

		return $output;
	}

	/**
	 * Prioritize our meta data
	 * ------------------------------------------------------------
	 *
	 * Loop through and select the highest ranking value for each attribute in our SproutSeo_MetaData model
	 *
	 * 1) Entry Override (Set by adding `id` override in Twig template code and using Meta Fields)
	 * 2) On-Page Override (Set in Twig template code)
	 * 3) Default (Set in control panel)
	 * 4) Global Fallback (Set in control panel)
	 * 5) Blank (Automatic)
	 *
	 * Once we have added all the content we need to be outputting to our array we will loop through that array and create the HTML we will output to our page.
	 *
	 * While we don't define HTML in our PHP as much as possible, the goal here is to be as easy to use as possible on the front end so we want to simplify the front end code to a single function and wrangle what we need to here.
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function getOptimizedMeta()
	{
		$prioritizeMetaLevels = array(
			'global' => null, //globalFallbackMetaTagModel
			'metaTagsGroup' => null,//metaTagsGroupMetaTagModel
			'code' => null,//codeOverrideMetaTagModel
			'entry' => null,//entryOverrideMetaTagModel
		);

		$prioritizedMetaTagModel = new SproutSeo_MetaTagsModel();

		$this->divider = craft()->plugins->getPlugin('sproutseo')->getSettings()->seoDivider;

		// Default to the Current URL
		$prioritizedMetaTagModel->canonical = SproutSeoOptimizeHelper::prepareCanonical($prioritizedMetaTagModel);

		foreach ($prioritizeMetaLevels as $meta => $model)
		{
			$metaTagModel = new SproutSeo_MetaTagsModel();

			if ($meta == 'global')
			{
				$metaTagModel = $metaTagModel->setMeta($meta);
			}
			else
			{
				$metaTagModel = $metaTagModel->setMeta($meta,$this->getMetaTagsFromTemplate($meta));
			}

			$prioritizeMetaLevels[$meta] = $metaTagModel;

			foreach ($prioritizedMetaTagModel->getAttributes() as $key => $value)
			{
				// Test for a value on each of our models in their order of priority
				if ($metaTagModel->getAttribute($key))
				{
					$prioritizedMetaTagModel[$key] = $metaTagModel[$key];
				}

				// Make sure all our strings are trimmed
				if (is_string($prioritizedMetaTagModel[$key]))
				{
					$prioritizedMetaTagModel[$key] = trim($prioritizedMetaTagModel[$key]);
				}
			}
		}

		// @todo - reorganize how this stuff works / robots need love.
		$prioritizedMetaTagModel->title  = SproutSeoOptimizeHelper::prepareAppendedSiteName(
			$prioritizedMetaTagModel,
			$prioritizeMetaLevels['metaTagsGroup'],
			$prioritizeMetaLevels['global'],
			$prioritizeMetaLevels['entry']
		);

		$prioritizedMetaTagModel->robots = SproutSeoOptimizeHelper::prepRobotsAsString($prioritizedMetaTagModel->robots);

		return $prioritizedMetaTagModel;
	}

	public function getKnowledgeGraphLinkedData()
	{
		$schemaRaw = sproutSeo()->schema->getGlobals();

		$schemaRaw = SproutSeo_SchemaModel::populateModel($schemaRaw);

		$schema                 = $schemaRaw->getJsonLd('identity');
		$schema['contactPoint'] = $schemaRaw->getJsonLd('contacts');
		$schema['sameAs']       = $schemaRaw->getJsonLd('social');

		$output = $this->prepareLinkedDataForHtml($schema);

		return TemplateHelper::getRaw($output);
	}

	public function prepareLinkedData(&$context)
	{
		// Grab our path, we're going to figure out what SEO meta data and
		// what Structured Data we need to output on the page based on this path
		$this->context = $context;
		$path    = craft()->request->getPath();
		$sitemap = sproutSeo()->sitemap->getAllSitemaps();

		// Get our meta values
		$meta = sproutSeo()->optimize->optimize();

		// Check the Twig $context for any values we need to process
		// to create Structured Data ($context->entry, $context->product, etc)

		// Get our structured data values
		$schema = sproutSeo()->schema->getGlobals();

		craft()->templates->setTemplatesPath(craft()->path->getPluginsPath());

		$schemaHtml = craft()->templates->render('sproutseo/templates/_special/schema', array(
			'schema' => $schema
		));

		craft()->templates->setTemplatesPath(craft()->path->getSiteTemplatesPath());

		// Process our Structured Data Schema Maps with the objects they match up with in the context

		// Prepare our html for the template
		$optimizedMeta = null;
		$optimizedMeta .= $meta;
		$optimizedMeta .= $schemaHtml;

		return TemplateHelper::getRaw($optimizedMeta);
	}

	/**
	 * @param $schema
	 *
	 * @return string
	 */
	protected function prepareLinkedDataForHtml($schema)
	{
		return '
<script type="application/ld+json">
' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '
</script>';
	}

	/**
	 * Prepare the default field type settings for the Meta Tag Group context.
	 *
	 * Display all of our fields manually for the Meta Tag Groups
	 *
	 * @return array
	 */
	public function getDefaultFieldTypeSettings()
	{
		return array(
			'optimizedTitleField'       => 'manually',
			'optimizedDescriptionField' => 'manually',
			'optimizedImageField'       => 'manually',
			'displayPreview'            => true,
			'showGeo'                   => true,
			'showRobots'                => true,
			'showOpenGraph'             => true,
			'showTwitter'               => true,
		);
	}
}
