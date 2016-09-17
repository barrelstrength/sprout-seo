<?php
namespace Craft;

/**
 * The Method class is an abstract class that defines the different meta levels available
 */
abstract class SproutSeo_MetaTagLevels extends BaseEnum
{
	// Constants
	// =========================================================================

	/**
	 * Lowest Priority
	 *
	 * globalFallbackMetaTagModel()
	 */
	const GlobalFallback = 'global';

	/**
	 * metadataGroupMetaTagModel
	 */
	const MetadataGroup = 'MetadataGroup';

	/**
	 * entryOverrideMetaTagModel
	 */
	const Entry = 'entry';

	/**
	 * Highest Priority
	 *
	 * codeOverrideMetaTagModel
	 */
	const Code = 'code';
}
