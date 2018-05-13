<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\services;

use yii\base\Component;
use craft\helpers\Json;
use Craft;


class Schema extends Component
{
    /**
     * Full schema.org core and extended vocabulary as described on schema.org
     * http://schema.org/docs/full.html
     *
     * @var array
     */
    public $vocabularies = [];

    /**
     * Returns an array of vocabularies based on the path provided
     * SproutSeo::$app->schema->getVocabularies('Organization.LocalBusiness.AutomotiveBusiness');
     *
     * @param null $path
     *
     * @return array
     */
    public function getVocabularies($path = null)
    {
        $jsonLdTreePath = Craft::getAlias('@sproutseolib/jsonld/tree.jsonld');

        $allVocabularies = Json::decode(file_get_contents($jsonLdTreePath));

        $this->vocabularies = $this->updateArrayKeys($allVocabularies['children'], 'name');

        if ($path) {
            return $this->getArrayByPath($this->vocabularies, $path);
        } else {
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
        foreach ($keys as $key) {
            if ($level == 1) {
                $array = $array[$key];
            } else {
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
        $newArray = [];

        foreach ($oldArray as $key => $value) {
            if (isset($value[$replaceKey])) {
                $key = $value[$replaceKey];
            }

            if (is_array($value)) {
                $value = $this->updateArrayKeys($value, $replaceKey);
            }

            $newArray[$key] = $value;
        }

        return $newArray;
    }
}
