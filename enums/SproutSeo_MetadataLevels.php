<?php
namespace Craft;

/**
 * All supported Metadata Levels to establish which metadata gets priority
 */
abstract class SproutSeo_MetadataLevels extends BaseEnum
{
	// Constants
	// =========================================================================

	/**
	 * GlobalMetadata
	 *
	 * UI Names: Globals, Global Metadata
	 * Internal Names: GlobalMetadata, globalMetadataModel
	 * Priority: 3, Lowest Priority
	 */
	const GlobalMetadata = 'global';

	/**
	 * UI Names: Sections, Section Metadata
	 * Internal Names: SectionMetadata, Section Metadata Sections, sectionMetadataModel
	 * Priority: 2
	 */
	const SectionMetadata = 'section';

	/**
	 * UI Names: Pages, SEO Optimize FieldType, Entry Metadata
	 * Internal Names: EntryMetadata, $entryMetadataModel
	 * Priority: 1
	 */
	const EntryMetadata = 'content';

	/**
	 * UI Names: Code Metadata, Code Overrides, Template Overrides
	 * Internal Names: CodeMetadata, $codeMetadataModel
	 * Priority: 0, Highest Priority
	 */
	const CodeMetadata = 'code';
}
