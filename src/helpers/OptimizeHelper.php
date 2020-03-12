<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\helpers;

use barrelstrength\sproutseo\SproutSeo;
use Craft;
use craft\base\Field;
use craft\elements\Asset;
use craft\fields\Assets;
use craft\helpers\UrlHelper;
use yii\base\Exception;
use yii\base\InvalidConfigException;

class OptimizeHelper
{
    /**
     * @param null $value
     *
     * @return string|null
     * @throws Exception
     * @throws InvalidConfigException
     */
    public static function getCanonical($value = null)
    {
        if ($value) {
            return $value;
        }

        return UrlHelper::siteUrl(Craft::$app->request->getPathInfo());
    }

    /**
     * @param      $id
     * @param null $transform
     *
     * @return null|string
     * @throws \Exception
     */
    public static function getAssetUrl($id, $transform = null)
    {
        $url = null;

        // If not, then process what we have to try to extract the URL
        if (0 !== mb_strpos($id, 'http')) {
            if (!is_numeric($id)) {
                throw new Exception('Meta Image override value "'.$id.'" must be an absolute url.');
            }

            /**
             * @var Asset $asset
             */
            $asset = Craft::$app->elements->getElementById($id);

            if ($asset !== null) {
                $transform = SproutSeo::$app->optimize->getSelectedTransform($transform);

                $imageUrl = Craft::$app->getAssets()->getAssetUrl($asset, $transform);

                // check to see if Asset already has full Site Url in folder Url
                if (strpos($imageUrl, 'http') !== false) {
                    $url = $asset->getUrl();
                } else {
                    $protocol = Craft::$app->request->getIsSecureConnection() ? 'https' : 'http';
                    $url = UrlHelper::urlWithScheme($imageUrl, $protocol);
                }
            } else {
                // If our selected asset was deleted, make sure it is null
                $url = null;
            }
        }

        return $url;
    }

    /**
     * @param $fieldId
     *
     * @return null
     */
    public static function getSelectedFieldForOptimizedMetadata($fieldId)
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