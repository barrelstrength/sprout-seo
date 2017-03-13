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
