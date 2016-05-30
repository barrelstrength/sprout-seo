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

	/**
	 * Save schema to database
	 *
	 * @param $schemaType
	 * @param $schema
	 *
	 * @return bool
	 */
	public function saveSchema($schemaType, $schema)
	{
		// @todo - what do we do if $schemaType doesn't have a value?

		$values = array(
			$schemaType => $schema->getSchema($schemaType, 'json')
		);

		$result = craft()->db->createCommand()->update('sproutseo_globals',
			$values,
			'id=:id', array(':id' => 1)
		);

		return $result;
	}

	/**
	 * Get global meta values
	 *
	 * @return BaseModel
	 */
	public function getGlobals()
	{
		$results = craft()->db->createCommand()
			->select('*')
			->from('sproutseo_globals')
			->queryRow();

		$results['identity']  = isset($results['identity']) ? JsonHelper::decode($results['identity']) : null;
		$results['contacts']  = isset($results['contacts']) ? JsonHelper::decode($results['contacts']) : null;
		$results['ownership'] = isset($results['ownership']) ? JsonHelper::decode($results['ownership']) : null;
		$results['social']    = isset($results['social']) ? JsonHelper::decode($results['social']) : null;

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

	public function installDefaultGlobals()
	{
		$locale = craft()->i18n->getLocaleById(craft()->language);

		$result = craft()->db->createCommand()->insert($tableName, array(
				'locale'    => $locale,
				'identy'    => null,
				'ownership' => null,
				'contacts'  => null,
				'social'    => null
			)
		);

		return $result;
	}

	/**
	 * @param        $array
	 * @param        $path
	 * @param string $separator
	 *
	 * @return mixed
	 */
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

	/**
	 * @param array $oldArray
	 * @param       $replaceKey
	 *
	 * @return array
	 */
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
