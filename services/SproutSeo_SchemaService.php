<?php
namespace Craft;

/**
 * Class SproutSeo_SchemaService
 *
 * @package Craft
 */
class SproutSeo_SchemaService extends BaseApplicationComponent
{
	public function prepareStructuredData($criteria, &$context)
	{
		// Take the values from our {% optimize %} tag, default to none.
		// @todo could potentially accept a string as well and check here
		$outputMeta   = isset($criteria['meta']) ? $criteria['meta'] : false;
		$outputSchema = isset($criteria['schema']) ? $criteria['schema'] : false;

		// Grab our path, we're going to figure out what SEO meta data and
		// what Structured Data we need to output on the page based on this path
		$path    = craft()->request->getPath();
		$sitemap = sproutSeo()->sitemap->getAllSitemaps();

		// Get our meta values
		$meta = sproutSeo()->meta->optimize();

		// Check the Twig $context for any values we need to process
		// to create Structured Data ($context->entry, $context->product, etc)

		// Get our structured data values
		$schemaMap = '{
			"name" : "{{ object.title }}",
			"url" : "members/{{ object.id }}",
			"sameAs" : [
				"{{ object.matrixField.blockType.fieldHandle :: repeat }}",
			]
		}';

		// Process our Structured Data Schema Maps with the objects they match up with in the context

		// Prepare our html for the template
		$meta = $meta . '
		
	<meta name="footprint" value="bigfoot">';

		return TemplateHelper::getRaw($meta);
	}

	public function getGlobalKnowledgeGraphMeta()
	{
		$results = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_globalmeta')
			->queryRow();

		$results['contacts']  = JsonHelper::decode($results['contacts']);
		$results['ownership'] = JsonHelper::decode($results['ownership']);
		$results['social']    = JsonHelper::decode($results['social']);

		return $results;
	}
}
