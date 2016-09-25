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
	 * Lowest Priority
	 *
	 * globalMetadataModel()
	 */
	const GlobalMetadata = 'global';

	/**
	 * sectionMetadataModel()
	 */
	const SectionMetadata = 'MetadataGroup';

	/**
	 * elementMetadataModel()
	 */
	const ElementMetadata = 'element';

	/**
	 * Highest Priority
	 *
	 * codeMetadataModel()
	 */
	const CodeMetadata = 'code';
}
