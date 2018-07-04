<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\services;

use barrelstrength\sproutseo\events\RegisterSchemasEvent;
use barrelstrength\sproutseo\schema\ContactPointSchema;
use barrelstrength\sproutseo\schema\CreativeWorkSchema;
use barrelstrength\sproutseo\schema\EventSchema;
use barrelstrength\sproutseo\schema\GeoSchema;
use barrelstrength\sproutseo\schema\ImageObjectSchema;
use barrelstrength\sproutseo\schema\IntangibleSchema;
use barrelstrength\sproutseo\schema\MainEntityOfPageSchema;
use barrelstrength\sproutseo\schema\OrganizationSchema;
use barrelstrength\sproutseo\schema\PersonSchema;
use barrelstrength\sproutseo\schema\PlaceSchema;
use barrelstrength\sproutseo\schema\PostalAddressSchema;
use barrelstrength\sproutseo\schema\ProductSchema;
use barrelstrength\sproutseo\schema\ThingSchema;
use barrelstrength\sproutseo\schema\WebsiteIdentityPersonSchema;
use barrelstrength\sproutseo\schema\WebsiteIdentityPlaceSchema;
use barrelstrength\sproutseo\schema\WebsiteIdentityWebsiteSchema;
use barrelstrength\sproutseo\schema\WebsiteIdentityOrganizationSchema;
use barrelstrength\sproutseo\SproutSeo;
use yii\base\Component;
use craft\helpers\Json;
use Craft;

class Schema extends Component
{
    const EVENT_REGISTER_SCHEMAS = 'registerSchemasEvent';

    /**
     * All registered Schema Types
     *
     * @var array
     */
    protected $schemaTypes = [];

    /**
     * All instantiated Schema Types indexed by class
     *
     * @var Schema[]
     */
    protected $schemas = [];

    /**
     * Full schema.org core and extended vocabulary as described on schema.org
     * http://schema.org/docs/full.html
     *
     * @var array
     */
    public $vocabularies = [];

    /**
     * @return array
     */
    public function getSchemasTypes()
    {
        $schemas = [
            WebsiteIdentityOrganizationSchema::class,
            WebsiteIdentityPersonSchema::class,
            WebsiteIdentityWebsiteSchema::class,
            WebsiteIdentityPlaceSchema::class,
            ContactPointSchema::class,
            ImageObjectSchema::class,
            MainEntityOfPageSchema::class,
            PostalAddressSchema::class,
            GeoSchema::class,
            ThingSchema::class,
            CreativeWorkSchema::class,
            EventSchema::class,
            IntangibleSchema::class,
            OrganizationSchema::class,
            PersonSchema::class,
            PlaceSchema::class
        ];

        if (Craft::$app->getPlugins()->getPlugin('commerce')) {
            $schemas[] = ProductSchema::class;
        }

        $event = new RegisterSchemasEvent([
            'schemas' => $schemas
        ]);

        $this->trigger(Schema::EVENT_REGISTER_SCHEMAS, $event);

        foreach ($event->schemas as $schema) {
            $this->schemaTypes[] = $schema;
        }

        return $this->schemaTypes;
    }

    /**
     * @return Schema[]
     */
    public function getSchemas()
    {
        $schemaTypes = $this->getSchemasTypes();

        foreach ($schemaTypes as $schemaClass) {
            $schema = new $schemaClass();
            $this->schemas[$schemaClass] = $schema;
        }

        uasort($this->schemas, function($a, $b) {
            /**
             * @var $a Schema
             * @var $b Schema
             */
            return $a->getName() <=> $b->getName();
        });

        return $this->schemas;
    }

    /**
     * Returns a list of available schema maps for display in a Main Entity select field
     *
     * @return array
     */
    public function getSchemaOptions()
    {
        $schemas = $this->getSchemas();

        foreach ($schemas as $schemaClass => $schema) {
            if ($schema->isUnlistedSchemaType()) {
                unset($schemas[$schemaClass]);
            }
        }

        // Get a filtered list of our default Sprout SEO schema
        $defaultSchema = array_filter($schemas, function($map) {
            /**
             * @var Schema $map
             */
            return stripos(get_class($map), 'barrelstrength\\sproutseo') !== false;
        });

        // Get a filtered list of of any custom schema
        $customSchema = array_filter($schemas, function($map) {
            /**
             * @var Schema $map
             */
            return stripos(get_class($map), 'barrelstrength\\sproutseo') === false;
        });

        // Build our options
        $schemaOptions = [
            '' => Craft::t('sprout-seo', 'None'), [
                'optgroup' => Craft::t('sprout-seo', 'Default Types')
            ]
        ];


        $schemaOptions = array_merge($schemaOptions, array_map(function($schema) {
            /**
             * @var Schema $schema
             */
            return [
                'label' => $schema->getName(),
                'type' => $schema->getType(),
                'value' => get_class($schema)
            ];
        }, $defaultSchema));

        if (count($customSchema)) {
            $schemaOptions[] = ['optgroup' => Craft::t('sprout-seo', 'Custom Types')];

            $schemaOptions = array_merge($schemaOptions, array_map(function($schema) {
                /**
                 * @var Schema $schema
                 */
                return [
                    'label' => $schema->getName(),
                    'type' => $schema->getType(),
                    'value' => get_class($schema),
                    'isCustom' => '1'
                ];
            }, $customSchema));
        }

        return $schemaOptions;
    }

    /**
     * Prepare an array of the optimized Meta
     *
     * @param array $schemas
     *
     * @return array[][]
     */
    public function getSchemaSubtypes($schemas)
    {
        $values = null;

        foreach ($schemas as $schema) {
            if (isset($schema['type'])) {
                $type = $schema['type'];

                // Create a generic first item in our list that matches the top level schema
                // We do this so we don't have a blank dropdown option for our secondary schemas
                $firstItem = [
                    $type => []
                ];

                if (!isset($schema['isCustom'])) {
                    $values[$schema['value']] = $this->getSchemaChildren($type);

                    if (count($values[$schema['value']])) {
                        $values[$schema['value']] = array_merge($firstItem, $values[$schema['value']]);
                    }
                }
            }
        }

        return $values;
    }

    /**
     * @param $type
     *
     * @return array
     */
    private function getSchemaChildren($type)
    {
        $tree = SproutSeo::$app->schema->getVocabularies($type);

        /**
         * @var array $children
         */
        $children = $tree['children'] ?? [];

        // let's assume 3 levels
        if (count($children)) {
            foreach ($children as $key => $level1) {
                $children[$key] = [];

                /**
                 * @var array $level1children
                 */
                $level1children = $level1['children'] ?? [];

                if (count($level1children)) {
                    foreach ($level1children as $key2 => $level2) {
                        $children[$key][$key2] = [];

                        /**
                         * @var array $level2children
                         */
                        $level2children = $level2['children'] ?? [];

                        if (count($level2children)) {
                            foreach ($level2children as $key3 => $level3) {
                                $children[$key][$key2][] = $key3;
                            }
                        }
                    }
                }
            }
        }

        return $children;
    }

    /**
     * Returns a schema map instance (based on $uniqueKey) or $default
     *
     * @param string $uniqueKey
     * @param null   $default
     *
     * @return mixed|null
     */
    public function getSchemaByUniqueKey($uniqueKey, $default = null)
    {
        $this->getSchemas();
        return array_key_exists($uniqueKey, $this->schemas) ? $this->schemas[$uniqueKey] : $default;
    }
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
