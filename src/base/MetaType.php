<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\base;

use craft\base\Component;
use craft\base\Field;

/**
 * @property string $handle
 * @property array  $attributesMapping
 * @property string $settingsHtml
 * @property array  $staticAttributes
 * @property string $iconPath
 * @property array  $rawData
 * @property array  $metaTagData
 */
abstract class MetaType extends Component
{
    use OptimizedTrait;

    public $rawDataOnly = false;

    /**
     * By default, expect metadata attributes to be matched to their exact name
     *
     * @return array
     */
    public function getAttributesMapping(): array
    {
        $mapping = [];

        foreach ($this->getAttributes() as $key => $attribute) {
            $mapping[$key] = $key;
        }

        return $mapping;
    }

    /**
     * @return string|null
     */
    public function getOptimizedTitle()
    {
        return $this->optimizedTitle;
    }

    /**
     * @param $value
     */
    public function setOptimizedTitle($value)
    {
        $this->optimizedTitle = $value;
    }

    /**
     * @return string|null
     */
    public function getOptimizedDescription()
    {
        return $this->optimizedDescription;
    }

    /**
     * @param $value
     */
    public function setOptimizedDescription($value)
    {
        $this->optimizedDescription = $value;
    }

    /**
     * @return int|null
     */
    public function getOptimizedImage()
    {
        return $this->optimizedImage;
    }

    /**
     * @param $value
     */
    public function setOptimizedImage($value)
    {
        $this->optimizedImage = $value;
    }

    /**
     * @return string|null
     */
    public function getOptimizedKeywords()
    {
        return $this->optimizedKeywords;
    }

    /**
     * @param $value
     */
    public function setOptimizedKeywords($value)
    {
        $this->optimizedKeywords = $value;
    }

    /**
     * The handle that will be used to reference this meta type in templates
     *
     * @return string
     */
    abstract public function getHandle(): string;

    /**
     * The icon that will display when displaying the Meta Details edit tab
     *
     * @return string
     */
    public function getIconPath(): string
    {
        return '';
    }

    /**
     * Whether this meta type supports meta details override settings. Implement getSettingsHtml() if so.
     *
     * @return bool
     */
    public function hasMetaDetails(): bool
    {
        return true;
    }

    /**
     * Whether to display a tab for users to edit meta details
     *
     * @return bool
     */
    public function showMetaDetailsTab(): bool
    {
        return false;
    }

    /**
     * The settings to display on the Meta Details edit tab
     *
     * @param Field $field
     *
     * @return string
     */
    public function getSettingsHtml(Field $field): string
    {
        return '';
    }

    /**
     * Just the attributes we need to save to the db
     *
     * @return array
     */
    public function getRawData(): array
    {
        $attributes = [];

        $this->rawDataOnly = true;

        foreach ($this->getAttributes() as $key => $attribute) {
            $attributes[] = $key;
        }

        return $attributes;
    }

    /**
     * Prepares the metadata for front-end use with calculated values
     *
     * @return array
     */
    public function getMetaTagData(): array
    {
        $tagData = [];

        foreach ($this->getAttributes() as $key => $value) {
            $getter = 'get'.ucfirst($key);
            if (method_exists($this, $getter)) {
                $value = $this->{$getter}();

                $metaTagName = $this->getMetaTagName($key);

                // Meta tag not supported in mapping.
                // For example, twitterTransform exists for settings but not on the front-end
                if (!$metaTagName) {
                    continue;
                }

                // Make sure all our strings are trimmed
                if (is_string($value)) {
                    $tagData[$metaTagName] = trim($value);
                } else {
                    $tagData[$metaTagName] = $value;
                }
            }
        }

        return $tagData;
    }

    /**
     * @param $handle
     *
     * @return mixed
     */
    protected function getMetaTagName($handle)
    {
        $tagNames = $this->getAttributesMapping();

        return $tagNames[$handle] ?? null;
    }
}
