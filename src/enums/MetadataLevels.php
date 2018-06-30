<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutseo\enums;

/**
 * Metadata Levels are used to establish which metadata gets priority
 */
abstract class MetadataLevels extends BaseEnum
{
    // Constants
    // =========================================================================

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
     * UI Names: Code Metadata, Code Overrides, Template Overrides
     * Internal Names: CodeMetadata, $codeMetadataModel
     * Priority: 0, Highest Priority
     */
    const CodeMetadata = 'code';

}
