<?php
namespace Craft;

/**
 * Class SproutSeo_SchemaService
 *
 * @package Craft
 */
class SproutSeo_SchemaService extends BaseApplicationComponent
{
	/**
	 * Full schema.org core and extended vocabulary as described on schema.org
	 * http://schema.org/docs/full.html
	 * 
	 * @var array
	 */
	public $vocabularies = array();
	
	public function saveSchema($target, $schema)
	{
		$values = array(
			$target => $schema->getSchema('identity', 'json')
		);

		craft()->db->createCommand()->update('sproutseo_globalmeta',
			$values,
			'id=:id', array(':id'=> 1)
		);

		return true;
	}

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
		$schema = $this->getGlobalKnowledgeGraphMeta();

		craft()->templates->setTemplatesPath(craft()->path->getPluginsPath());

		$schemaHtml = craft()->templates->render('sproutseo/templates/_special/schema', array(
			'schema' => $schema
		));

		craft()->templates->setTemplatesPath(craft()->path->getSiteTemplatesPath());

		// Process our Structured Data Schema Maps with the objects they match up with in the context

		// Prepare our html for the template
		$optimizedMeta = null;

		if ($outputMeta)
		{
			$optimizedMeta .= $meta;
		}

		if ($outputSchema)
		{
			$optimizedMeta .= $schemaHtml;
		}

		return TemplateHelper::getRaw($optimizedMeta);
	}

	public function prepareKnowledgeGraphStructuredData()
	{
		$schemaRaw = $this->getGlobalKnowledgeGraphMeta();

		$schema['@context'] = "http://schema.org";
		$schema['@type']    = $schemaRaw['knowledgeGraph']['type'];
		$schema['name']     = $schemaRaw['knowledgeGraph']['name'];
		$schema['url']      = $schemaRaw['knowledgeGraph']['url'];

		$contactPoints = array();
		foreach ($schemaRaw['contacts'] as $contact)
		{
			$contactPoints[] = array(
				'@type'       => 'ContactPoint',
				'telephone'   => $contact['phone'],
				'contactType' => $contact['type']
			);
		}

		$schema['contactPoint'] = $contactPoints;

		$socialLinks = array();
		foreach ($schemaRaw['social'] as $socialLink)
		{
			$socialLinks[] = $socialLink['url'];
		}

		$schema['sameAs'] = $socialLinks;

		$output = '
<script type="application/ld+json">
' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '
</script>';

		return TemplateHelper::getRaw($output);
	}

	public function getGlobalKnowledgeGraphMeta()
	{
		$results = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_globalmeta')
			->queryRow();


		$results['knowledgeGraph'] = JsonHelper::decode($results['knowledgeGraph']);
		$results['contacts']       = JsonHelper::decode($results['contacts']);
		$results['ownership']      = JsonHelper::decode($results['ownership']);
		$results['social']         = JsonHelper::decode($results['social']);

		$schema = SproutSeo_SchemaModel::populateModel($results);

		return $schema;
	}

	/**
	 * Returns an array of vocabularies based on the path provided
	 * sproutSeo()->schema->getVocabularies('Organization.LocalBusiness.AutomotiveBusiness');
	 *
	 * @param null $path
	 *
	 * @return array
	 */
	public function getVocabularies($path = null)
	{
		$jsonLdTreePath = craft()->path->getPluginsPath() . 'sproutseo/resources/jsonld/tree.jsonld';

		$allVocabularies = JsonHelper::decode(file_get_contents($jsonLdTreePath));

		$this->vocabularies = $this->updateArrayKeys($allVocabularies['children'], 'name');

		if ($path)
		{
			return $this->getArrayByPath($this->vocabularies, $path);
		}
		else
		{
			return $this->vocabularies;
		}
	}

	protected function getArrayByPath($array, $path, $separator = '.')
	{
		$keys = explode($separator, $path);

		$level = 1;
		foreach ($keys as $key)
		{
			if ($level == 1)
			{
				$array = $array[$key];
			}
			else
			{
				$array = $array['children'][$key];
			}

			$level++;
		}

		return $array;
	}


	protected function updateArrayKeys(array $oldArray, $replaceKey)
	{
		$newArray = array();

		foreach ($oldArray as $key => $value)
		{
			if (isset($value[$replaceKey]))
			{
				$key = $value[$replaceKey];
			}

			if (is_array($value))
			{
				$value = $this->updateArrayKeys($value, $replaceKey);
			}

			$newArray[$key] = $value;
		}

		return $newArray;
	}
}
