<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\base;

use barrelstrength\sproutseo\SproutSeo;
use Craft;
use craft\base\Field;
use craft\elements\Asset;
use craft\errors\SiteNotFoundException;
use craft\fields\Assets;
use craft\helpers\UrlHelper;
use Throwable;
use yii\base\Exception;

trait MetaImageTrait
{
    /**
     * @param null $image
     *
     * @return mixed|string|null
     * @throws Throwable
     * @throws Exception
     */
    public function normalizeImageValue($image = null)
    {
        $element = SproutSeo::$app->optimize->element;
        $elementMetadataField = SproutSeo::$app->optimize->elementMetadataField;

        $optimizedImageFieldSetting = $elementMetadataField->optimizedImageField ?? 'manually';

        switch (true) {
            // Manual Image
            case ($optimizedImageFieldSetting === 'manually'):
                // ElementMetadata Field post data: If we have an array grab the first item, if not, just leave the $image value as is
                if (is_array($image)) {
                    $image = $image[0] ?? null;
                }
                break;

            // Custom Image Field
            case (is_numeric($optimizedImageFieldSetting)):
                $image = $this->getSelectedFieldForOptimizedMetadata($elementMetadataField->id);
                break;

            // Custom Value
            default:
                $image = Craft::$app->view->renderObjectTemplate($optimizedImageFieldSetting, $element);
                break;
        }

        return $image;
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

    /**
     * Can be used to prepare the asset metadata for front-end use.
     * Depending on the scenario this method can return just the URL or
     * a list of image attributes. If returning all data, the return value
     * is an array and must be assigned to a list() not a simple $variable.
     *
     * @param      $image
     * @param null $transform
     *
     * @return Asset|string|string[]|null
     * @throws Exception
     * @throws SiteNotFoundException
     */
    public function prepareAssetMetaData($image, $transform = null, $urlOnly = true)
    {
        // If it's an URL, use it.
        if (0 === mb_strpos($image, 'http')) {
            return $image;
        }

        if (!is_numeric($image)) {
            SproutSeo::warning('Meta image value "'.$image.'" cannot be identified. Must be an absolute URL or an Asset ID.');
            return null;
        }

        // If the siteUrl is https or the current request is https, use it.
        $scheme = parse_url(UrlHelper::baseSiteUrl(), PHP_URL_SCHEME);
        $transformSettings = $transform ? SproutSeo::$app->optimize->getSelectedTransform($transform) : null;

        // If our selected asset was deleted, make sure it is null
        $absoluteUrl = null;

        $asset = Craft::$app->assets->getAssetById($image);

        if (!$asset || !$asset->getUrl()) {
            return null;
        }

        $imageUrl = (string)$asset->getUrl();

        if ($transformSettings) {
            $imageUrl = (string)$asset->getUrl($transformSettings);
        }

        // check to see if Asset already has full Site Url in folder Url
        if (UrlHelper::isAbsoluteUrl($imageUrl)) {
            $absoluteUrl = $imageUrl;
        } elseif (UrlHelper::isProtocolRelativeUrl($imageUrl)) {
            $absoluteUrl = $scheme.':'.$imageUrl;
        } else {
            $absoluteUrl = UrlHelper::siteUrl($imageUrl);
        }

        if (!$urlOnly) {
            $imageWidth = $asset->width ?? null;
            $imageHeight = $asset->height ?? null;
            $imageType = $asset->mimeType ?? null;

            if ($transformSettings) {
                $imageWidth = $asset->getWidth($transformSettings);
                $imageHeight = $asset->getHeight($transformSettings);
            }
        }

        if (Craft::$app->request->getIsSecureConnection()) {
            $secureUrl = preg_replace('/^http:/i', 'https:', $absoluteUrl);
            $absoluteUrl = $secureUrl;
        }

        if ($urlOnly) {
            return $absoluteUrl;
        }

        return [
            $absoluteUrl,
            $imageWidth,
            $imageHeight,
            $imageType
        ];
    }
}
