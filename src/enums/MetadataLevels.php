<?php
/**
 * @link https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license https://craftcms.github.io/license
 */

namespace barrelstrength\sproutseo\enums;

/**
 * Metadata Levels are used to establish which metadata gets priority
 */
abstract class MetadataLevels
{
    /**
     * Global Metadata
     *
     * UI Names: Globals, Global Metadata
     * Internal Names: GlobalMetadata, globalMetadataModel
     * Priority: 3, Lowest Priority
     */
    const GlobalMetadata = 'global';

    /**
     * Element Metadata
     *
     * UI Names: Pages, Element Metadata
     * Internal Names: ElementMetadata, $elementMetadataModel
     * Priority: 1
     */
    const ElementMetadata = 'element';

    /**
     * UI Names: Template Metadata, Template Overrides, Code Overrides
     * Internal Names: TemplateMetadata, $templateMetadataModel
     * Priority: 0, Highest Priority
     */
    const TemplateMetadata = 'template';

}
