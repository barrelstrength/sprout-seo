<?php
namespace Craft;

class SproutSeo_OptimizeService extends BaseApplicationComponent
{
	public $divider;
	public $context;
	public $templateMeta = array();

	/**
	 * Get all metadata (Meta Tags and Structured Data) for the page
	 *
	 * @param $context
	 *
	 * @return \Twig_Markup
	 */
	public function getMetadata(&$context)
	{
		$optimizedMetadata = null;

		// Grab our path, we're going to figure out what SEO meta data and
		// what Structured Data we need to output on the page based on this path
		$this->context = $context;
		$path          = craft()->request->getPath();

		$globals = sproutSeo()->schema->getGlobals();

		// get the sitemap info + urlFormat + $context->entry  $context->product ..
		$sitemapInfo = sproutSeo()->schema->getSitemapInfo($context);

		// Get our meta values
		$prioritizedMetaTagModel = sproutSeo()->metaTags->getPrioritizedMetaTagModel(
			$sitemapInfo
		);
		$sitemapInfo['prioritizedMetaTagModel'] = $prioritizedMetaTagModel;
		$sitemapInfo['globals'] = $globals;

		$metaHtml = sproutSeo()->metaTags->getMetaTagHtml($prioritizedMetaTagModel);

		// Check the Twig $context for any values we need to process
		// to create Structured Data ($context->entry, $context->product, etc)

		// Get our structured data values
		$schemaHtml = sproutSeo()->schema->getStructureDataHtml($sitemapInfo);

		// Process our Structured Data Schema Maps with the objects they match up with in the context
		$mainEntitySchemaHtml = sproutSeo()->schema->getMainEntityStructuredDataHtml($sitemapInfo);

		// Prepare our html for the template
		$optimizedMetadata .= $metaHtml;
		$optimizedMetadata .= $schemaHtml;
		$optimizedMetadata .= $mainEntitySchemaHtml;

		return TemplateHelper::getRaw($optimizedMetadata);
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
