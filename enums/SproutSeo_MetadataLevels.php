<?php
namespace Craft;

/**
 * Metadata Levels are used to establish which metadata gets priority
 */
abstract class SproutSeo_MetadataLevels extends BaseEnum
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
	 * Section Metadata
	 *
	 * UI Names: Sections, Section Metadata
	 * Internal Names: SectionMetadata, Section Metadata Sections, sectionMetadataModel
	 * Priority: 2
	 */
	const SectionMetadata = 'section';

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
