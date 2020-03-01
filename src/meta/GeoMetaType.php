<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\meta;

use barrelstrength\sproutseo\base\MetaType;
use barrelstrength\sproutseo\SproutSeo;
use Craft;
use craft\base\Field;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;

/**
 * Implements all attributes used in geo metadata
 */
class GeoMetaType extends MetaType
{
    /**
     * @var string|null
     */
    protected $region;

    /**
     * @var string|null
     */
    protected $placename;

    /**
     * @var string|null
     */
    protected $position;

    /**
     * @var string|null
     */
    protected $latitude;

    /**
     * @var string|null
     */
    protected $longitude;

    public static function displayName(): string
    {
        return Craft::t('sprout-seo', 'Geo');
    }

    public function attributes(): array
    {
        $attributes = parent::attributes();
        $attributes[] = 'region';
        $attributes[] = 'placename';
        $attributes[] = 'position';
        $attributes[] = 'longitude';
        $attributes[] = 'latitude';

        return $attributes;
    }

    /**
     * @return array
     */
    public function getAttributesMapping(): array
    {
        return [
            'region' => 'geo.region',
            'placename' => 'geo.placename',
            'position' => 'geo.position'
        ];
    }

    /**
     * @return string|null
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param $value
     */
    public function setRegion($value)
    {
        $this->region = $value;
    }

    /**
     * @return string|null
     */
    public function getPlacename()
    {
        return $this->placename;
    }

    /**
     * @param $value
     */
    public function setPlacename($value)
    {
        $this->placename = $value;
    }

    /**
     * @return string|null
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param $value
     */
    public function setPosition($value)
    {
        $this->position = $value;
    }

    /**
     * @return string|null
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param $value
     */
    public function setLatitude($value)
    {
        $this->latitude = $value;
    }

    /**
     * @return string|null
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param $value
     */
    public function setLongitude($value)
    {
        $this->longitude = $value;
    }

    public function getHandle(): string
    {
        return 'geo';
    }

    public function getIconPath(): string
    {
        return '@sproutbaseicons/map-marker-alt.svg';
    }

    /**
     * @param Field $field
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getSettingsHtml(Field $field): string
    {
        return Craft::$app->getView()->renderTemplate('sprout-seo/_components/fields/elementmetadata/blocks/geo', [
            'meta' => $this,
            'field' => $field
        ]);
    }

    public function showMetaDetailsTab(): bool
    {
        return SproutSeo::$app->optimize->elementMetadataField->showGeo;
    }

    /**
     * @return array
     */
    public function getMetaTagData(): array
    {
        $tagData = [];

        foreach ($this->getAttributes() as $key => $value) {
            if ($key === 'latitude' or $key === 'longitude') {
                break;
            }

            $value = $this->{$key};

            if ($key === 'position') {
                $value = $this->prepareGeoPosition();
            }

            if ($value) {
                $tagData[$this->getMetaTagName($key)] = $value;
            }
        }

        return $tagData;
    }

    /**
     * Set the geo 'position' attribute based on the 'latitude' and 'longitude'
     *
     * @return string|null
     */
    protected function prepareGeoPosition()
    {
        if ($this->latitude && $this->longitude) {
            return $this->latitude.';'.$this->longitude;
        }

        return $this->position;
    }
}
