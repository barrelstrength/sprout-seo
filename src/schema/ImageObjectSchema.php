<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
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

        $prioritizedMetadataModel = $this->prioritizedMetadataModel;

        if (isset($prioritizedMetadataModel)) {
            $openGraphMetaType = $prioritizedMetadataModel->getMetaTypes('openGraph');

            if (isset($openGraphMetaType)) {
                if ($openGraphMetaType->getOgImage()) {
                    $this->addUrl('url', $openGraphMetaType->getOgImage());
                }

                if ($openGraphMetaType->getOgImageHeight()) {
                    $this->addNumber('height', $openGraphMetaType->getOgImageHeight());
                }

                if ($openGraphMetaType->getOgImageWidth()) {
                    $this->addNumber('width', $openGraphMetaType->getOgImageWidth());
                }
            }
        }
    }
}