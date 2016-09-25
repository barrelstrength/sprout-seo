<?php
namespace Craft;

/**
 * The Method class is an abstract class that defines the different meta levels available
 */
abstract class SproutSeo_MetaTagLevels extends BaseEnum
{
	// Constants
	// =========================================================================

	//
	// SectionMetadata
	// ElementMetadata
	// CodeMetadata

	/**
	 * Lowest Priority
	 *
	 * globalMetadataModel()
	 */
	const GlobalMetadata = 'global';

	/**
	 * sectionMetadataModel()
	 */
	const MetadataGroup = 'MetadataGroup';

	/**
	 * elementMetadataModel()
	 */
	const Entry = 'entry';

	/**
	 * Highest Priority
	 *
	 * codeMetadataModel()
	 */
	const Code = 'code';
}
