<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\schema;

use barrelstrength\sproutseo\base\Schema;

class ImageObjectSchema extends Schema
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Image Object';
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return 'ImageObject';
    }

    /**
     * @return bool
     */
    public function isUnlistedSchemaType(): bool
    {
        return true;
    }

    public function addProperties()
    {
        $image = $this->element;

        if (!$image) {
            return null;
        }

        $height = $image['height'] ?? null;
        $width = $image['width'] ?? null;

        $this->addUrl('url', $image['url']);
        $this->addNumber('height', $height);
        $this->addNumber('width', $width);

        // let's check for any imageTransform

        $prioritizedMetadataModel = $this->prioritizedMetadataModel;

        /** @noinspection UnSafeIsSetOverArrayInspection */
        if (isset($prioritizedMetadataModel->ogTransform) && $prioritizedMetadataModel->ogTransform) {
            if ($prioritizedMetadataModel->ogImage) {
                $this->addUrl('url', $prioritizedMetadataModel->ogImage);
            }

            if ($prioritizedMetadataModel->ogImageHeight) {
                $this->addNumber('height', $prioritizedMetadataModel->ogImageHeight);
            }

            if ($prioritizedMetadataModel->ogImageWidth) {
                $this->addNumber('width', $prioritizedMetadataModel->ogImageWidth);
            }
        }
    }
}