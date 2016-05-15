<?php
namespace Craft;

class SproutSeo_SchemaModel extends BaseModel
{
	public $type = null;

	protected function defineAttributes()
	{
		return array(
			'schema' => AttributeType::Mixed,

			'knowledgeGraph' => AttributeType::Mixed,
			'contacts' => AttributeType::Mixed,
			'social' => AttributeType::Mixed,
		);
	}

	/**
	 * Factory to return schema of any type
	 *
	 * @param        $target
	 * @param string $format
	 *
	 * @return string
	 */
	public function getSchema($target, $format = 'array')
	{
		$targetMethod = 'get' . ucfirst($target);

		$schema = $this->{$targetMethod}();

		if ($format == 'json')
		{
			return JsonHelper::encode($schema);
		}

		return $schema;
	}


	// Supported Schema Types
	// =========================================================================

	public function getIdentity()
	{
		$identity = $this->prepareSchemaObject();

		$identity['name']        = $this->schema['thing']['name'];
		$identity['description'] = $this->schema['thing']['description'];
		$identity['url']         = $this->schema['thing']['url'];

		return $identity;
	}

	public function getType()
	{
		$this->getIdentity();

		return $this->type;
	}

	public function getOrganization()
	{

	}

	public function getPerson()
	{

	}

	public function getWebsite()
	{

	}

	public function getPlace()
	{

	}


	// Custom Schema Types
	// =========================================================================

	public function getSchemaMap($object, $mapId)
	{

	}


	// Protected Methods
	// =========================================================================

	protected function prepareSchemaObject()
	{
		$this->type = $this->schema['thing']['type'];

		return array(
			"@context" => "http://schema.org",
			"@type"    => $this->type
		);
	}
}
