<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\base;

use barrelstrength\sproutseo\helpers\OptimizeHelper;
use barrelstrength\sproutseo\SproutSeo;
use Craft;
use craft\base\Field;
use craft\fields\Assets;
use yii\base\Exception;

trait OptimizedTrait
{
    /**
     * @var string|null
     */
    protected $optimizedTitle;

    /**
     * @var string|null
     */
    protected $appendTitleValue;

    /**
     * @var string|null
     */
    protected $optimizedDescription;

    /**
     * @var int|null
     */
    protected $optimizedImage;

    /**
     * @var string|null
     */
    protected $optimizedKeywords;

    /**
     * @var string|null
     */
    protected $canonical;

    /**
     * @return string|null
     * @throws Exception
     */
    public function getCanonical()
    {
        return OptimizeHelper::getCanonical($this->canonical);
    }

    /**
     * @param $value
     */
    public function setCanonical($value)
    {
        $this->canonical = $value;
    }

    /**
     * @param $fieldId
     *
     * @return null
     */
    public function getSelectedFieldForOptimizedMetadata($fieldId)
    {
        $value = null;

        $element = SproutSeo::$app->optimize->element;

        if (is_numeric($fieldId)) {
            /**
             * @var Field $field
             */
            $field = Craft::$app->fields->getFieldById($fieldId);

            // Does the field exist on the element?
            if ($field && isset($element->{$field->handle})) {
                $elementValue = $element->{$field->handle};
                if (get_class($field) === Assets::class) {
                    $value = isset($elementValue[0]) ? $elementValue[0]->id : null;
                } else {
                    $value = $elementValue;
                }
            }
        }

        return $value;
    }
}
